<?php
/**
 * Ad Management Functions
 * 
 * This file contains all ad-related functions for the classifieds CMS application
 * including creating, updating, deleting, and retrieving ads.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all categories
 * 
 * @return array Array of categories
 */
function getCategories() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Create a new ad
 * 
 * @param int $userId User ID
 * @param array $adData Ad data
 * @return array Result array with success status and message
 */
function createAd($userId, $adData) {
    try {
        $pdo = getDBConnection();
        
        // Validate required fields
        $required = ['title', 'description', 'category_id'];
        foreach ($required as $field) {
            if (empty($adData[$field])) {
                return ['success' => false, 'message' => 'Missing required field: ' . $field];
            }
        }
        
        // Validate category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND is_active = 1");
        $stmt->execute([$adData['category_id']]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Invalid category selected'];
        }
        
        // Prepare data
        $title = trim($adData['title']);
        $description = trim($adData['description']);
        $categoryId = (int)$adData['category_id'];
        $price = !empty($adData['price']) ? (float)$adData['price'] : null;
        $location = trim($adData['location'] ?? '');
        $contactEmail = trim($adData['contact_email'] ?? '');
        $contactPhone = trim($adData['contact_phone'] ?? '');
        $imageFilename = $adData['image_filename'] ?? null;
        
        // Insert ad
        $stmt = $pdo->prepare("
            INSERT INTO ads (user_id, category_id, title, description, price, location, 
                           contact_email, contact_phone, image_filename) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId, $categoryId, $title, $description, $price, 
            $location, $contactEmail, $contactPhone, $imageFilename
        ]);
        
        return ['success' => true, 'message' => 'Ad created successfully', 'ad_id' => $pdo->lastInsertId()];
        
    } catch (PDOException $e) {
        error_log("Error creating ad: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create ad. Please try again.'];
    }
}

/**
 * Update an existing ad
 * 
 * @param int $adId Ad ID
 * @param int $userId User ID (for ownership verification)
 * @param array $adData Updated ad data
 * @return array Result array with success status and message
 */
function updateAd($adId, $userId, $adData) {
    try {
        $pdo = getDBConnection();
        
        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
        $stmt->execute([$adId, $userId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Ad not found or access denied'];
        }
        
        // Validate required fields
        $required = ['title', 'description', 'category_id'];
        foreach ($required as $field) {
            if (empty($adData[$field])) {
                return ['success' => false, 'message' => 'Missing required field: ' . $field];
            }
        }
        
        // Validate category exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND is_active = 1");
        $stmt->execute([$adData['category_id']]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Invalid category selected'];
        }
        
        // Prepare data
        $title = trim($adData['title']);
        $description = trim($adData['description']);
        $categoryId = (int)$adData['category_id'];
        $price = !empty($adData['price']) ? (float)$adData['price'] : null;
        $location = trim($adData['location'] ?? '');
        $contactEmail = trim($adData['contact_email'] ?? '');
        $contactPhone = trim($adData['contact_phone'] ?? '');
        $status = $adData['status'] ?? 'active';
        
        // Update ad
        $sql = "
            UPDATE ads 
            SET title = ?, description = ?, category_id = ?, price = ?, location = ?, 
                contact_email = ?, contact_phone = ?, status = ?, updated_at = CURRENT_TIMESTAMP
        ";
        $params = [$title, $description, $categoryId, $price, $location, $contactEmail, $contactPhone, $status];
        
        // Handle image update if provided
        if (isset($adData['image_filename'])) {
            $sql .= ", image_filename = ?";
            $params[] = $adData['image_filename'];
        }
        
        $sql .= " WHERE id = ? AND user_id = ?";
        $params[] = $adId;
        $params[] = $userId;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true, 'message' => 'Ad updated successfully'];
        
    } catch (PDOException $e) {
        error_log("Error updating ad: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update ad. Please try again.'];
    }
}

/**
 * Delete an ad
 * 
 * @param int $adId Ad ID
 * @param int $userId User ID (for ownership verification)
 * @return array Result array with success status and message
 */
function deleteAd($adId, $userId) {
    try {
        $pdo = getDBConnection();
        
        // Get ad details for cleanup
        $stmt = $pdo->prepare("SELECT image_filename FROM ads WHERE id = ? AND user_id = ?");
        $stmt->execute([$adId, $userId]);
        $ad = $stmt->fetch();
        
        if (!$ad) {
            return ['success' => false, 'message' => 'Ad not found or access denied'];
        }
        
        // Delete ad
        $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
        $stmt->execute([$adId, $userId]);
        
        // Delete associated image file if exists
        if ($ad['image_filename']) {
            $imagePath = __DIR__ . '/../assets/uploads/' . $ad['image_filename'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        return ['success' => true, 'message' => 'Ad deleted successfully'];
        
    } catch (PDOException $e) {
        error_log("Error deleting ad: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete ad. Please try again.'];
    }
}

/**
 * Get ads by user
 * 
 * @param int $userId User ID
 * @param string $status Status filter (optional)
 * @return array Array of ads
 */
function getUserAds($userId, $status = null) {
    try {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT a.*, c.name as category_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            WHERE a.user_id = ?
        ";
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching user ads: " . $e->getMessage());
        return [];
    }
}

/**
 * Get ad by ID
 * 
 * @param int $adId Ad ID
 * @param int $userId User ID (optional, for ownership check)
 * @return array|null Ad data or null if not found
 */
function getAdById($adId, $userId = null) {
    try {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT a.*, c.name as category_name, u.username, u.first_name, u.last_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = ?
        ";
        $params = [$adId];
        
        if ($userId !== null) {
            $sql .= " AND a.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $ad = $stmt->fetch();
        
        // Increment view count if not owner viewing
        if ($ad && ($userId === null || $ad['user_id'] != $userId)) {
            $updateStmt = $pdo->prepare("UPDATE ads SET views_count = views_count + 1 WHERE id = ?");
            $updateStmt->execute([$adId]);
        }
        
        return $ad;
        
    } catch (PDOException $e) {
        error_log("Error fetching ad: " . $e->getMessage());
        return null;
    }
}

/**
 * Handle image upload
 * 
 * @param array $file $_FILES array element
 * @return array Result array with success status and filename
 */
function uploadAdImage($file) {
    $uploadDir = __DIR__ . '/../assets/uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed'];
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to save uploaded file'];
    }
}
?>

