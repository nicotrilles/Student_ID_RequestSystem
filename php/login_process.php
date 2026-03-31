<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['request_id']) ? trim($_POST['request_id']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === 'Admin' && $password === 'Admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ../Admin.html');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Result</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .result-box { background: #fff; border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.1); padding: 32px; width: min(420px, 90%); text-align: center; }
        .result-box h1 { margin-bottom: 16px; }
        .result-box p { margin-bottom: 24px; color: #444; }
        .result-box a { display: inline-block; padding: 12px 24px; border-radius: 8px; text-decoration: none; color: #fff; background: #2E7D32; }
        .error { color: #d9534f; margin-bottom: 24px; }
    </style>
</head>
<body>
    <div class="result-box">
        <?php if ($error): ?>
            <h1>Login Failed</h1>
            <p class="error"><?= htmlspecialchars($error) ?></p>
            <a href="../adminLogin.html">Back to Login</a>
        <?php else: ?>
            <h1>Processing...</h1>
            <p>If you are not redirected automatically, <a href="../Admin.html">click here</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>