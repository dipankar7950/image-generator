<?php
require_once 'includes/config.php';
require_once 'includes/ai-integration.php';
requireLogin();

// Default image sizes
$image_sizes = [
    'square' => ['1024x1024', '512x512', '256x256'],
    'portrait' => ['1024x1536', '768x1024', '512x768'],
    'landscape' => ['1536x1024', '1024x768', '768x512']
];

// Handle image generation
$generated_image = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prompt = trim($_POST['prompt']);
    $size = $_POST['size'];
    $style = $_POST['style'];
    $enhance_details = isset($_POST['enhance_details']);
    
    if (empty($prompt)) {
        $error = 'Please enter a prompt';
    } else {
        // Generate image using AI service
        $result = generateAIImage($prompt, $size, $style, $enhance_details);
        
        if ($result['success']) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO generated_images (user_id, prompt, image_path, size, style) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $prompt, $result['image_path'], $size, $style]);
            
            $generated_image = $result['image_path'];
        } else {
            $error = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Image - AI Image Generator</title>
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

    <main class="generate-container">
        <h2>Generate AI Image</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="generate-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="prompt">Describe what you want to generate</label>
                    <textarea id="prompt" name="prompt" rows="4" placeholder="A beautiful sunset over mountains with a lake reflection..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="size">Image Size</label>
                    <select id="size" name="size" required>
                        <option value="">Select Size</option>
                        <optgroup label="Square">
                            <?php foreach ($image_sizes['square'] as $size): ?>
                                <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Portrait">
                            <?php foreach ($image_sizes['portrait'] as $size): ?>
                                <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Landscape">
                            <?php foreach ($image_sizes['landscape'] as $size): ?>
                                <option value="<?php echo $size; ?>"><?php echo $size; ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="style">Art Style</label>
                    <select id="style" name="style">
                        <option value="realistic">Realistic</option>
                        <option value="cartoon">Cartoon</option>
                        <option value="painting">Painting</option>
                        <option value="digital">Digital Art</option>
                        <option value="abstract">Abstract</option>
                        <option value="vintage">Vintage</option>
                    </select>
                </div>
                
                <div class="form-group checkbox">
                    <input type="checkbox" id="enhance_details" name="enhance_details" value="1">
                    <label for="enhance_details">Enhance Details</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Generate Image</button>
            </form>
        </div>
        
        <?php if ($generated_image): ?>
            <div class="generated-result">
                <h3>Generated Image</h3>
                <div class="image-container">
                    <img src="<?php echo $generated_image; ?>" alt="Generated Image">
                    <div class="image-actions">
                        <a href="<?php echo $generated_image; ?>" download class="btn btn-secondary">Download</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>