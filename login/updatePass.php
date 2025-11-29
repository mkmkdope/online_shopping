<?php
session_start();
include '../sb_base.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    $errors = [];

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters with letters, numbers, and special characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $response['message'] = implode("\n", $errors);
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT username FROM users WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $response['message'] = 'Invalid or expired token.';
            echo json_encode($response);
            exit;
        }

        $username = $user['username'];
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ?, token = NULL, token_expirydate = NULL WHERE username = ?");
        $update->execute([$hashed, $username]);

        $response['success'] = true;
        $response['message'] = 'Password updated successfully.';
    } catch (PDOException $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}
echo json_encode($response);
