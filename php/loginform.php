<?php
session_start();

// --- Database connection ---
$host = "localhost";
$user = "db_username";
$pass = "db_password";
$db   = "db_name";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- Process login ---
if (isset($_POST['login'])) {
    $request_id = trim($_POST['request_id']);
    $password   = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE request_id = ? LIMIT 1");
    $stmt->bind_param("s", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();

        // If password is hashed in DB
        if (password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['request_id'] = $student['request_id'];

            // Redirect to dashboard (adjust path)
            header("Location: ../dashboard.php");
            exit;
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Request ID not found!";
    }

    $stmt->close();
}

$conn->close();
?>
