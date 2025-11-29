<?php
session_start();

include __DIR__ . '/../sb_base.php';
include __DIR__ . '/../login/Email.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $token = bin2hex(random_bytes(16));
        $expiry = date('Y-m-d H:i:s', time() + 86400);
        if ($result) {
            $stmt = $conn->prepare("UPDATE users SET token = ?, token_expirydate = ? WHERE email = ?");
            $stmt->execute([$token, $expiry, $email]);
        } else {
            $response['message'] = 'Email not found.';
            echo json_encode($response);
            exit;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $username = $user['username'];
            $resetLink = "https://localhost:8443/index.php?token=" . urlencode($token);

            $subject = "Password Reset Request";
            $body = "Hello " . htmlspecialchars($username) . ",\n\n";
            $body .= "To reset your password, please click the link below:\n";
            $body .= $resetLink . "\n\n";
            $body .= "If you did not request a password reset, please ignore this email.\n\n";
            $body .= "Best regards,\nYour Website Team";

            if (sendEmail($email, $subject, $body)) {
                $response['success'] = true;
                $response['message'] = 'Password reset link sent to your email.';
            } else {
                $response['message'] = 'Failed to send email. Please try again later.';
            }
        } else {
            $response['message'] = 'Email not found.';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    echo json_encode($response);
}
exit;
