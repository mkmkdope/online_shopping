<?php
session_start();
include '../sb_base.php';

$userId = 1; // Keep original setting

// ----------------------------
// Initialize error variables
// ----------------------------
$_err = [];

// ----------------------------
// Handle POST requests
// ----------------------------
if (is_post()) {

    // ----------------------------
    // Update profile
    // ----------------------------
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if ($username === '') {
            $_err['profile'] = "Username cannot be empty.";
        } elseif ($email === '') {
            $_err['profile'] = "Email cannot be empty.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->execute([$username, $email, $userId]);

            // ====== Save session and redirect ======
            temp('success_profile', "Profile updated successfully!");
            redirect();
        }
    }

    // ----------------------------
    // Update password
    // ----------------------------
    if (isset($_POST['update_password'])) {
        $current = trim($_POST['current_password']);
        $new = trim($_POST['new_password']);
        $confirm = trim($_POST['confirm_password']);

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        $dbPassword = $row ? $row['password_hash'] : '';

        if ($current === '') {
            $_err['password'] = "Current password cannot be empty!";
        } elseif ($current !== $dbPassword) {
            $_err['password'] = "Current password incorrect!";
        } elseif ($new !== $confirm) {
            $_err['password'] = "New password and confirm password do not match!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
            $stmt->execute([$new, $userId]);

            // ====== Save session and redirect ======
            temp('success_password', "Password updated successfully!");
            redirect();
        }
    }

    // ----------------------------
    // Upload profile photo
    // ----------------------------
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $uploadFile = $uploadDir . $fileName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedTypes)) {
            $_err['photo'] = "Image type not allowed.";
        } elseif (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
            $stmt = $pdo->prepare("UPDATE users SET photo_url=? WHERE id=?");
            $stmt->execute([$fileName, $userId]);

            // ====== Save session and redirect ======
            temp('success_photo', "Profile photo uploaded successfully!");
            redirect();
        } else {
            $_err['photo'] = "Failed to upload photo.";
        }
    }
}

// ----------------------------
// Get user information
// ----------------------------
$stmt = $pdo->prepare("SELECT username, email, photo_url FROM users WHERE id=?");
$stmt->execute([$userId]);
$row = $stmt->fetch();

$username = $row ? $row['username'] : "";
$email = $row ? $row['email'] : "";
$photo_url = $row ? $row['photo_url'] : "";
$profileImg = $photo_url ? 'uploads/' . $photo_url : 'images/default.png';

// ----------------------------
// Get temporary success messages
// ----------------------------
$success_profile = temp('success_profile');
$success_password = temp('success_password');
$success_photo = temp('success_photo');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile Settings</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>/* Form section */
.content-section {
    background: #fff;
    border-radius: 6px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.content-section h2 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

form label {
    font-weight: 600;
    margin-bottom: 0.3rem;
    color: #2c3e50;
}

form input[type="text"],
form input[type="email"],
form input[type="password"],
form input[type="file"],
form textarea {
    width: 100%;
    padding: 0.8rem 1rem;
    border-radius: 6px;
    border: 1px solid #ddd;
    font-size: 1rem;
    transition: border 0.2s, box-shadow 0.2s;
}

form input:focus,
form textarea:focus {
    border-color: #3498db;
    box-shadow: 0 0 5px rgba(52,152,219,0.3);
    outline: none;
}

button[type="submit"],
button[type="reset"] {
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

button[type="submit"] {
    background-color: #3498db;
    color: #fff;
}

button[type="submit"]:hover {
    background-color: #2980b9;
}

button[type="reset"] {
    background-color: #ecf0f1;
    color: #2c3e50;
}

button[type="reset"]:hover {
    background-color: #d0d7de;
}

/* Table styling */
.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

th, td {
    padding: 0.8rem 1rem;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
    color: #2c3e50;
}

th {
    background-color: #f8f9fa;
    font-weight: 600;
}

tr:hover {
    background-color: #f1f3f6;
}

.status {
    padding: 0.3rem 0.6rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-active {
    background-color: rgba(46,204,113,0.2);
    color: #27ae60;
}

.status-pending {
    background-color: rgba(241,196,15,0.2);
    color: #f39c12;
}

</style>
</head>
<body>
<div class="main-content">
    <h1 class="page-title">Profile Settings</h1>

    <!-- Update Profile -->
    <div class="content-section">
        <h2>Update Profile Details</h2>

        <?php if ($success_profile): ?>
            <div class="success-box"><?= $success_profile ?></div>
        <?php endif; ?>

        <?php if (isset($_err['profile'])): ?>
            <p class="error"><?= $_err['profile'] ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

            <button type="submit" name="update_profile">Save Changes</button>
            <button type="reset">Reset</button>
        </form>
    </div>

    <!-- Upload Profile Photo -->
    <div class="content-section">
        <h2>Upload Profile Photo</h2>

        <?php if ($success_photo): ?>
            <div class="success-box"><?= $success_photo ?></div>
        <?php endif; ?>

        <?php if (isset($_err['photo'])): ?>
            <p class="error"><?= $_err['photo'] ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <img src="<?= $profileImg ?>" style="width:150px;height:150px;border-radius:50%;border:2px solid #ddd;">
            <input type="file" name="photo" accept="image/*">
            <button type="submit">Upload Photo</button>
        </form>
    </div>

    <!-- Update Password -->
    <div class="content-section">
        <h2>Update Password</h2>

        <?php if ($success_password): ?>
            <div class="success-box"><?= $success_password ?></div>
        <?php endif; ?>

        <?php if (isset($_err['password'])): ?>
            <p class="error"><?= $_err['password'] ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Current Password</label>
            <input type="password" name="current_password">

            <label>New Password</label>
            <input type="password" name="new_password">

            <label>Confirm Password</label>
            <input type="password" name="confirm_password">

            <button type="submit" name="update_password">Update Password</button>
        </form>
    </div>
</div>
</body>
</html>
