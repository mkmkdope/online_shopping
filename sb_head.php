<?php
// start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SB | Sell Book Online</title>
    <link rel="stylesheet" href="/css/sb_style.css">
</head>
<body>
    <header>
        <div class="logo">SB</div>
        <div class="header-right">
            <nav>
                <ul>
                    <li><a href="/index.php">Home</a></li>
                    <li><a href="/page/product.php">Books</a></li>
                    <li><a href="/page/category.php">Categories</a></li>
                    <li><a href="/page/about.php">About</a></li>
                    <li><a href="/page/adminProduct.php">temp-product management</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/page/cart_view.php">Cart</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile-dropdown">
                    <button class="profile-icon" onclick="toggleProfileDropdown()" title="Profile">
                        <?php
                            $profileImage = '/images/login.jpg';
                            if (!empty($_SESSION['profile_photo'])) {
                                $profileImage = '/page/uploads/profiles/' . $_SESSION['profile_photo'];
                            }
                        ?>
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile" class="profile-image">
                    </button>
                    <div class="dropdown-menu" id="profileDropdown">
                        <a href="/page/<?= ($_SESSION['user_type'] ?? '') === 'admin' ? 'admin_profile.php' : 'user_profile.php' ?>">Profile</a>
                        <a href="/login/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login/index.php" class="login-link">Login</a>
            <?php endif; ?>
        </div>
    </header>
    <script>
        function toggleProfileDropdown() {
            document.getElementById('profileDropdown').classList.toggle('show');
        }
        
        // close dropdown when clicking outside the profile icon
        window.onclick = function(event) {
            if (!event.target.matches('.profile-icon') && !event.target.closest('.profile-dropdown')) {
                var dropdown = document.getElementById('profileDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>