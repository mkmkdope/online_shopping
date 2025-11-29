<?php
// Do NOT call session_start() here
// It should be called in login.php, index.php, etc.

include __DIR__ . '/../sb_base.php';

// Do NOT call session_start() here if already called in index.php/login.php
if (!isset($_SESSION['username']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $stmt = $conn->prepare("SELECT username, role, remember_token FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        if ($user['remember_token'] && password_verify($token, $user['remember_token'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if (basename($_SERVER['PHP_SELF']) === 'login.php') {
                header("Location: home.php");
                exit;
            }
            break;
        }
    }
}
