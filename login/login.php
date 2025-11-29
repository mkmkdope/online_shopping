<?php
session_start();
include __DIR__ . '/../sb_base.php';
include __DIR__ . '/RememberMe.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = !empty($_POST['remember_me']);
    $errors = [];

    // Validate
    if (!preg_match('/^(?=.*[A-Za-z])[A-Za-z0-9_]+$/', $username)) {
        $response['message'] = 'Username must contain letters and numbers only.';
        echo json_encode($response);
        exit;
    }
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters with letters, numbers, and special characters.';
    }
    if (!empty($errors)) {
        $response['message'] = implode("\n", $errors);
        echo json_encode($response);
        exit;
    }

    // Failed attempts check
    if (!isset($_SESSION['failed_attempts'])) $_SESSION['failed_attempts'] = 0;
    if (!isset($_SESSION['last_failed'])) $_SESSION['last_failed'] = 0;

    if ($_SESSION['failed_attempts'] >= 3 && time() - $_SESSION['last_failed'] < 300) {
        $remaining = 300 - (time() - $_SESSION['last_failed']);
        $response['message'] = "Too many failed attempts. Try again in {$remaining} seconds.";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            $_SESSION['failed_attempts'] = 0;

            if ($rememberMe && $user['role'] !== 'admin') {
                $token = bin2hex(random_bytes(16));
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE username = ?");
                $stmt->execute([$hashedToken, $username]);

                // $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                // if (setcookie('remember_me', $token, time() + (86400 * 30), "/", "", $secure, true)) {
                //     error_log("✅ Cookie set: $token, secure=$secure");
                // } else {
                //     error_log("❌ Cookie failed to set!");
                // }
            } else {
                setcookie('remember_me', '', time() - 3600, "/"); // remove cookie
                $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE username = ?");
                $stmt->execute([$username]);
            }


            $response['success'] = true;
            $response['redirect'] = ($user['role'] === 'admin') ? '/page/adminPanel.php' : '/home.php';
        } else {
            $_SESSION['failed_attempts'] += 1;
            $_SESSION['last_failed'] = time();
            $left = 3 - $_SESSION['failed_attempts'];

            $response['message'] = ($left > 0)
                ? "Invalid username or password. {$left} attempt(s) left."
                : "Too many failed attempts. Try again in 5 minutes.";
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit;
