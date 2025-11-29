<?php
session_start();
include __DIR__ . '/../sb_base.php';
include __DIR__ . '/Email.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = 'user';

    $errors = [];

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/', $username)) {
        $errors[] = 'Username must contain letters and numbers only.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters with letters, numbers, and special characters.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if ($errors) {
        $response['message'] = implode("\n", $errors);
        echo json_encode($response);
        exit;
    }

    $image = (!empty($_FILES['profile_img']) && $_FILES['profile_img']['error'] === 0) ?
        file_get_contents($_FILES['profile_img']['tmp_name']) :
        file_get_contents('../images/login.jpg');

    $hash_pass = password_hash($password, PASSWORD_DEFAULT);

    try {
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetchColumn() > 0) {
            $response['message'] = 'Username or email already exists.';
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_img, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hash_pass, $image, $role]);

        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $response['success'] = true;
        $response['message'] = "Welcome to Online Shopping, $username!";

        sendEmail($email, 'Registration Successful', $response['message']);
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);
exit;
