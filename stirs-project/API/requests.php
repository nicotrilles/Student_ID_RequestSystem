<?php
/**
 * STIRS – Requests API
 * POST /api/requests.php?action=submit   → student submits form
 * GET  /api/requests.php?action=track    → student tracks by tracking# + studentId
 * GET  /api/requests.php?action=list     → admin gets all/filtered requests
 * GET  /api/requests.php?action=stats    → admin dashboard counts
 * POST /api/requests.php?action=decide   → admin approves/rejects
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    // ─────────────────────────────────────────
    case 'submit':
    // ─────────────────────────────────────────
        $raw = json_decode(file_get_contents('php://input'), true);
        if (!$raw) { echo json_encode(['error' => 'No data']); exit; }

        $db = getDB();

        // Generate unique tracking number
        do {
            $year    = date('Y');
            $rand    = str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $tracking = "REQ-{$year}-{$rand}";
            $check   = $db->query("SELECT id FROM requests WHERE tracking_number='$tracking' LIMIT 1");
        } while ($check->num_rows > 0);

        $s  = fn($v) => $db->real_escape_string(trim($v ?? ''));
        $bd = $s($raw['birthdate'] ?? date('Y-m-d'));

        $sql = "INSERT INTO requests
            (tracking_number, request_type, student_status, student_id, full_name, birthdate,
             department, year_level, address, contact,
             emergency_name, emergency_address, emergency_contact,
             reason, photo, signature, status, submitted_at)
        VALUES (
            '{$tracking}',
            '{$s($raw['requestType'])}',
            '{$s($raw['studentStatus'])}',
            '{$s($raw['studentId'])}',
            '{$s($raw['fullName'])}',
            '{$bd}',
            '{$s($raw['department'])}',
            " . intval($raw['yearLevel'] ?? 1) . ",
            '{$s($raw['address'])}',
            '{$s($raw['contact'])}',
            '{$s($raw['emergencyName'])}',
            '{$s($raw['emergencyAddress'])}',
            '{$s($raw['emergencyContact'])}',
            '{$s($raw['reason'])}',
            '{$s($raw['photo'])}',
            '{$s($raw['signature'])}',
            'Pending',
            NOW()
        )";

        if ($db->query($sql)) {
            echo json_encode(['success' => true, 'trackingNumber' => $tracking]);
        } else {
            echo json_encode(['error' => $db->error]);
        }
        $db->close();
        break;

    // ─────────────────────────────────────────
    case 'track':
    // ─────────────────────────────────────────
        $db = getDB();
        $tn = $db->real_escape_string($_GET['trackingNumber'] ?? '');
        $si = $db->real_escape_string($_GET['studentId'] ?? '');

        $res = $db->query("
            SELECT tracking_number, student_id, full_name, department, year_level,
                   request_type, reason, status, admin_comment,
                   DATE_FORMAT(submitted_at,'%M %d, %Y %h:%i %p') AS submitted_at,
                   DATE_FORMAT(processed_at,'%M %d, %Y %h:%i %p') AS processed_at
            FROM requests
            WHERE tracking_number='{$tn}' AND student_id='{$si}'
            LIMIT 1
        ");

        if ($res->num_rows === 0) {
            echo json_encode(['found' => false]);
        } else {
            $row = $res->fetch_assoc();
            $row['found'] = true;
            echo json_encode($row);
        }
        $db->close();
        break;

    // ─────────────────────────────────────────
    case 'list':
    // ─────────────────────────────────────────
        // Admin only – in production add session check here
        $db     = getDB();
        $status = $db->real_escape_string($_GET['status'] ?? 'all');
        $q      = '%' . $db->real_escape_string($_GET['q'] ?? '') . '%';

        $where = "WHERE (tracking_number LIKE '{$q}' OR student_id LIKE '{$q}' OR full_name LIKE '{$q}')";
        if ($status !== 'all') $where .= " AND status='{$status}'";

        $res = $db->query("
            SELECT id, tracking_number, student_id, full_name, department, year_level,
                   request_type, student_status, reason, status, admin_comment,
                   DATE_FORMAT(submitted_at,'%b %d, %Y %h:%i %p') AS submitted_at,
                   DATE_FORMAT(processed_at,'%b %d, %Y %h:%i %p') AS processed_at
            FROM requests
            {$where}
            ORDER BY submitted_at DESC
        ");

        $rows = [];
        while ($row = $res->fetch_assoc()) $rows[] = $row;
        echo json_encode($rows);
        $db->close();
        break;

    // ─────────────────────────────────────────
    case 'detail':
    // ─────────────────────────────────────────
        $db = getDB();
        $id = intval($_GET['id'] ?? 0);
        $res = $db->query("SELECT * FROM requests WHERE id={$id} LIMIT 1");
        if ($res->num_rows === 0) { echo json_encode(['error'=>'Not found']); exit; }
        echo json_encode($res->fetch_assoc());
        $db->close();
        break;

    // ─────────────────────────────────────────
    case 'stats':
    // ─────────────────────────────────────────
        $db  = getDB();
        $res = $db->query("
            SELECT
                COUNT(*)                              AS total,
                SUM(status='Pending')                 AS pending,
                SUM(status='Approved')                AS approved,
                SUM(status='Rejected')                AS rejected,
                SUM(DATE(submitted_at)=CURDATE())     AS today
            FROM requests
        ");
        echo json_encode($res->fetch_assoc());

        // Recent 5
        $recent = $db->query("
            SELECT tracking_number, full_name, status,
                   DATE_FORMAT(submitted_at,'%b %d %h:%i%p') AS submitted_at
            FROM requests ORDER BY submitted_at DESC LIMIT 5
        ");
        $rows = [];
        while ($row = $recent->fetch_assoc()) $rows[] = $row;
        // Re-encode with recent included
        $db->close();

        // redo cleanly
        $db2 = getDB();
        $s2  = $db2->query("SELECT COUNT(*) AS total, SUM(status='Pending') AS pending, SUM(status='Approved') AS approved, SUM(status='Rejected') AS rejected, SUM(DATE(submitted_at)=CURDATE()) AS today FROM requests");
        $stats = $s2->fetch_assoc();
        $r2 = $db2->query("SELECT tracking_number, full_name, status, DATE_FORMAT(submitted_at,'%b %d %h:%i%p') AS submitted_at FROM requests ORDER BY submitted_at DESC LIMIT 5");
        $recent2 = [];
        while ($row = $r2->fetch_assoc()) $recent2[] = $row;
        $stats['recent'] = $recent2;
        echo json_encode($stats);
        $db2->close();
        break;

    // ─────────────────────────────────────────
    case 'decide':
    // ─────────────────────────────────────────
        $raw  = json_decode(file_get_contents('php://input'), true);
        $db   = getDB();
        $id   = intval($raw['id'] ?? 0);
        $stat = $db->real_escape_string($raw['status'] ?? '');
        $note = $db->real_escape_string($raw['adminComment'] ?? '');

        if (!in_array($stat, ['Approved','Rejected'])) {
            echo json_encode(['error'=>'Invalid status']); exit;
        }

        $db->query("
            UPDATE requests
            SET status='{$stat}', admin_comment='{$note}', processed_at=NOW()
            WHERE id={$id}
        ");

        echo json_encode(['success' => true, 'affected' => $db->affected_rows]);
        $db->close();
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
