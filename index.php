<?php
session_start();
include __DIR__ . '/sb_base.php';

if (!isset($_SESSION['username']) && !empty($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $stmt = $conn->prepare("SELECT username, role, remember_token FROM users WHERE remember_token IS NOT NULL");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $found = false;
    foreach ($users as $user) {
        if (password_verify($token, $user['remember_token'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Remove invalid cookie
        setcookie('remember_me', '', time() - 3600, "/");
    }
}

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SB | Sell Book Online</title>
    <link rel="stylesheet" href="/css/sb_style.css?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/sb_script.js"></script>
</head>

<body>
    <header style="background-color: transparent;">
        <div class=" logo">SB</div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="/page/product.php">Books</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </header>

    <img src="/images/login_background.jpg" alt="background" class="background-image">

    <div class="login-container">
        <img src="/images/login.jpg" alt="User" class="user-icon">

        <form id="updatePassword-form" style="display:none;" enctype="multipart/form-data" data-valid="<?php echo $validToken ? 'true' : 'false'; ?>">
            <h2>Update Password</h2>
            <div class="password-wrapper">
                <input type="password" name="password" placeholder="New Password" required />
                <span class="eye-icon">&#128065;</span>
            </div>

            <div class="password-wrapper">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required />
                <span class="eye-icon">&#128065;</span>
            </div>
            <button type="submit">Update Password</button>

            <div class="login-links">
                <span class="link-span" data-get="login">Back to Login</span>
            </div>
        </form>

        <form id="login-form" enctype="multipart/form-data">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="Username" required />
            <div class="password-wrapper">
                <input type="password" name="password" placeholder="Password" required />
                <span class="eye-icon">&#128065;</span>
            </div>
            <button type="submit">Login</button>

            <div class="login-links">
                <label><input type="checkbox" name="remember_me" />Remember Me</label>
                <span class="link-span" data-get="forgotPassword">Forgot Password?</span>
            </div>

            <div class="login-links">
                <span class="link-span" data-get="register">Create an Account</span>
            </div>
        </form>

        <form id="register-form" style="display:none;" enctype="multipart/form-data">
            <h2>Register</h2>
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />

            <div class="password-wrapper">
                <input type="password" name="password" placeholder="Password" required />
                <span class="eye-icon">&#128065;</span>
            </div>

            <div class="password-wrapper">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required />
                <span class="eye-icon">&#128065;</span>
            </div>

            <input type="file" name="profile_img" accept="image/*" />
            <button type="submit">REGISTER</button>

            <div id="register-message" style="display:none; padding:10px; margin-top:10px; border-radius:5px;"></div>

            <div class="login-links">
                <span class="link-span" data-get="login">Back to Login</span>
            </div>
        </form>


        <form id="request-form">
            <h2>Update Password</h2>
            <input type="email" name="email" placeholder="Email" required />
            <button type="submit">Confirm</button>
        </form>
    </div>
</body>

</html>