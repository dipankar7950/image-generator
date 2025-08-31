<?php
require_once 'includes/config.php';
requireAdmin();

// Get all users
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_images FROM generated_images");
$stmt->execute();
$total_images = $stmt->fetch()['total_images'];

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = $_GET['id'];
    
    if ($action == 'delete' && $user_id != $_SESSION['user_id']) {
        // Delete user and their images
        $stmt = $pdo->prepare("SELECT image_path FROM generated_images WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_images = $stmt->fetchAll();
        
        // Delete image files
        foreach ($user_images as $image) {
            if (file_exists($image['image_path'])) {
                unlink($image['image_path']);
            }
        }
        
        // Delete user's profile image if exists
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user['profile_image'] && file_exists($user['profile_image'])) {
            unlink($user['profile_image']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM generated_images WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        header("Location: admin.php?message=User+deleted+successfully");
        exit();
    } elseif ($action == 'toggle_admin' && $user_id != $_SESSION['user_id']) {
        // Toggle admin status
        $stmt = $pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
        $stmt->execute([$user_id]);
        
        header("Location: admin.php?message=User+status+updated");
        exit();
    }
}

// Show message if exists
$message = '';
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AI Image Generator</title>
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
                <li><a href="admin.php">Admin</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container">
        <h2>Admin Dashboard</h2>
        
        <?php if (!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo $total_users; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Images Generated</h3>
                <p class="stat-number"><?php echo $total_images; ?></p>
            </div>
        </div>
        
        <div class="users-list">
            <h3>User Management</h3>
            
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Joined</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="admin.php?action=toggle_admin&id=<?php echo $user['id']; ?>" class="action-btn edit-btn">
                                        <?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                    </a>
                                    <a href="admin.php?action=delete&id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        Delete
                                    </a>
                                <?php else: ?>
                                    <span>Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 AI Image Generator. All rights reserved.</p>
    </footer>
</body>
</html>