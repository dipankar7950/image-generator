<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Image Generator</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">AI Image Generator</div>
            <ul>
                <?php if (isLoggedIn()): ?>
                    <li><a href="generate.php">Generate</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Create Stunning AI-Generated Images</h1>
            <p>Transform your ideas into visual masterpieces with our advanced AI technology</p>
            <?php if (!isLoggedIn()): ?>
                <div class="cta-buttons">
                    <a href="signup.php" class="btn btn-primary">Get Started</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                </div>
            <?php else: ?>
                <a href="generate.php" class="btn btn-primary">Generate Images</a>
            <?php endif; ?>
        </section>

        <section class="features">
            <h2>Features</h2>
            <div class="feature-grid">
                <div class="feature">
                    <h3>Multiple Sizes</h3>
                    <p>Generate images in various aspect ratios and resolutions</p>
                </div>
                <div class="feature">
                    <h3>Advanced Options</h3>
                    <p>Customize style, details, and other parameters</p>
                </div>
                <div class="feature">
                    <h3>User Profiles</h3>
                    <p>Save and manage your generated images</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 AI Image Generator. All rights reserved.</p>
    </footer>
</body>
</html>