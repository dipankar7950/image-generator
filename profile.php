<?php
require_once 'includes/config.php';
requireLogin();

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $date_of_birth = $_POST['date_of_birth'];
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($full_name) || empty($date_of_birth)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if username or email already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            // Handle profile image upload
            $profile_image = $user['profile_image'];
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/profile_images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Remove old profile image if exists
                if ($profile_image && file_exists($profile_image)) {
                    unlink($profile_image);
                }
                
                $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $file_path)) {
                    $profile_image = $file_path;
                }
            }
            
            // Update password if provided
            $password_update = '';
            $params = [$username, $email, $full_name, $profile_image, $date_of_birth, $user_id];
            
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $error = 'Passwords do not match';
                } elseif (strlen($_POST['password']) < 6) {
                    $error = 'Password must be at least 6 characters long';
                } else {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $password_update = ', password = ?';
                    array_splice($params, 5, 0, [$hashed_password]);
                }
            }
            
            if (empty($error)) {
                // Update user
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, profile_image = ?, date_of_birth = ? $password_update WHERE id = ?");
                
                if ($stmt->execute($params)) {
                    $_SESSION['username'] = $username;
                    $success = 'Profile updated successfully';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } else {
                    $error = 'Error updating profile. Please try again.';
                }
            }
        }
    }
}

// Get user's generated images
$stmt = $pdo->prepare("SELECT * FROM generated_images WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$generated_images = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AI Image Generator</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">AI Image Generator</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="generate.php">Generate</a></li>
                <li><a href="profile.php">Profile</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="profile-container">
        <h2>Your Profile</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-content">
            <div class="profile-form">
                <h3>Edit Profile</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $user['date_of_birth']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image">Profile Image</label>
                        <?php if ($user['profile_image']): ?>
                            <div class="current-image">
                                <img src="<?php echo $user['profile_image']; ?>" alt="Profile Image" style="max-width: 150px; display: block; margin-bottom: 10px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div class="profile-stats">
                <h3>Your Statistics</h3>
                <div class="stat-card">
                    <h4>Images Generated</h4>
                    <p class="stat-number"><?php echo count($generated_images); ?></p>
                </div>
                
                <div class="stat-card">
                    <h4>Member Since</h4>
                    <p class="stat-date"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="generated-images">
            <h3>Your Generated Images</h3>
            
            <?php if (count($generated_images) > 0): ?>
                <div class="image-grid">
                    <?php foreach ($generated_images as $image): ?>
                        <div class="image-item">
                            <img src="<?php echo $image['image_path']; ?>" alt="Generated Image">
                            <div class="image-info">
                                <p class="prompt"><?php echo substr($image['prompt'], 0, 50) . (strlen($image['prompt']) > 50 ? '...' : ''); ?></p>
                                <p class="size">Size: <?php echo $image['size']; ?></p>
                                <p class="date"><?php echo date('M j, Y', strtotime($image['created_at'])); ?></p>
                                <div class="image-actions">
                                    <a href="<?php echo $image['image_path']; ?>" download class="btn btn-secondary">Download</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>You haven't generated any images yet. <a href="generate.php">Generate your first image</a>!</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 AI Image Generator. All rights reserved.</p>
    </footer>
</body>
</html>