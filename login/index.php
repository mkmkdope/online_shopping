<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SB | Sell Book Online</title>
    <link rel="stylesheet" href="/css/sb_style.css">
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

        <form id="login-form" method="POST" action="login.php">
            <h2>Login</h2>
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <span id="togglePassword" class="eye-icon">&#128065;</span>
            <button type="submit">Login</button>

            <div class="login-links">
                <label><input type="checkbox" />Remember Me</label>
                <span class="forgotPassword link-span" data-get="forgotPassword">Forgot Password?</span>
            </div>

            <div class="login-links">
                <span class="link-span" data-get="register">Create an Account</span>
            </div>
        </form>

        <form id="register-form" style="display:none;" method="POST" action="register.php">
            <h2>Register</h2>
            <input type="text" name="username" placeholder="Username" required />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <button type="submit">REGISTER</button>

            <div class="login-links">
                <span class="link-span" data-get="login">Back to Login</span>
            </div>
        </form>

        <form id="updatePassword-form" style="display:none;" method="POST" action="updatePassword.php">
            <h2>Update Password</h2>
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <button type="submit">Confirm</button>

            <div class="login-links">
                <span class="link-span" data-get="login">Back to Login</span>
            </div>
        </form>
    </div>
</body>

</html>