<?php
/**
 * Helper functions for the AI Image Generator application
 */

/**
 * Sanitize user input
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Generate a random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Format file size in human readable format
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Validate image file
 */
function validateImageFile($file) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return 'Invalid file type. Only JPG, PNG, and GIF images are allowed.';
    }
    
    if ($file['size'] > $max_size) {
        return 'File is too large. Maximum size is 5MB.';
    }
    
    return true;
}

/**
 * Get user's generated images count
 */
function getUserImageCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM generated_images WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch()['count'];
}

/**
 * Get recent generated images
 */
function getRecentImages($pdo, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT gi.*, u.username 
        FROM generated_images gi 
        JOIN users u ON gi.user_id = u.id 
        ORDER BY gi.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Check if username is available
 */
function isUsernameAvailable($pdo, $username, $exclude_user_id = null) {
    if ($exclude_user_id) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $exclude_user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
    }
    
    return $stmt->rowCount() === 0;
}

/**
 * Check if email is available
 */
function isEmailAvailable($pdo, $email, $exclude_user_id = null) {
    if ($exclude_user_id) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $exclude_user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }
    
    return $stmt->rowCount() === 0;
}

/**
 * Get user by ID
 */
function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Get user by username
 */
function getUserByUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

/**
 * Log activity
 */
function logActivity($pdo, $user_id, $activity) {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$user_id, $activity]);
}

/**
 * Get activity log
 */
function getActivityLog($pdo, $limit = 50) {
    $stmt = $pdo->prepare("
        SELECT al.*, u.username 
        FROM activity_log al 
        JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Send email notification
 */
function sendEmailNotification($to, $subject, $message) {
    // In a real application, you would use a proper email library
    // This is a placeholder implementation
    $headers = "From: no-reply@ai-image-generator.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Generate pagination links
 */
function generatePagination($current_page, $total_pages, $url) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<div class="pagination">';
    
    // Previous link
    if ($current_page > 1) {
        $pagination .= '<a href="' . $url . '?page=' . ($current_page - 1) . '" class="page-link">&laquo; Previous</a>';
    }
    
    // Page links
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $pagination .= '<span class="page-link current">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . $url . '?page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }
    
    // Next link
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $url . '?page=' . ($current_page + 1) . '" class="page-link">Next &raquo;</a>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}

/**
 * Get image dimensions from size string
 */
function getImageDimensions($size) {
    list($width, $height) = explode('x', $size);
    return ['width' => (int)$width, 'height' => (int)$height];
}

/**
 * Calculate aspect ratio
 */
function calculateAspectRatio($width, $height) {
    $gcd = function($a, $b) use (&$gcd) {
        return $b ? $gcd($b, $a % $b) : $a;
    };
    
    $divisor = $gcd($width, $height);
    return ($width / $divisor) . ':' . ($height / $divisor);
}

/**
 * Validate date of birth
 */
function validateDateOfBirth($date) {
    $min_age = 13; // Minimum age requirement
    $dob = new DateTime($date);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
    
    return $age >= $min_age;
}

/**
 * Get user age
 */
function getUserAge($date_of_birth) {
    $dob = new DateTime($date_of_birth);
    $now = new DateTime();
    return $now->diff($dob)->y;
}
?>