<?php
/**
 * Search and Browse Functions
 * 
 * This file contains functions for searching and browsing classified ads
 * for public users and visitors.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Search and browse ads with filters
 * 
 * @param array $filters Search filters
 * @return array Array of ads
 */
function searchAds($filters = []) {
    try {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT a.*, c.name as category_name, u.username, u.first_name, u.last_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'active'
        ";
        $params = [];
        
        // Search by keyword
        if (!empty($filters['keyword'])) {
            $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        // Filter by category
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        
        // Filter by location
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        
        // Filter by price range
        if (!empty($filters['min_price'])) {
            $sql .= " AND a.price >= ?";
            $params[] = (float)$filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND a.price <= ?";
            $params[] = (float)$filters['max_price'];
        }
        
        // Sort options
        $sortBy = $filters['sort'] ?? 'newest';
        switch ($sortBy) {
            case 'oldest':
                $sql .= " ORDER BY a.created_at ASC";
                break;
            case 'price_low':
                $sql .= " ORDER BY a.price ASC, a.created_at DESC";
                break;
            case 'price_high':
                $sql .= " ORDER BY a.price DESC, a.created_at DESC";
                break;
            case 'popular':
                $sql .= " ORDER BY a.views_count DESC, a.created_at DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY a.created_at DESC";
                break;
        }
        
        // Pagination
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error searching ads: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of ads matching filters
 * 
 * @param array $filters Search filters
 * @return int Total count
 */
function getAdsCount($filters = []) {
    try {
        $pdo = getDBConnection();
        
        $sql = "
            SELECT COUNT(*) as total 
            FROM ads a 
            WHERE a.status = 'active'
        ";
        $params = [];
        
        // Search by keyword
        if (!empty($filters['keyword'])) {
            $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        // Filter by category
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        
        // Filter by location
        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
        }
        
        // Filter by price range
        if (!empty($filters['min_price'])) {
            $sql .= " AND a.price >= ?";
            $params[] = (float)$filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND a.price <= ?";
            $params[] = (float)$filters['max_price'];
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return (int)$result['total'];
        
    } catch (PDOException $e) {
        error_log("Error counting ads: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get featured/recent ads for homepage
 * 
 * @param int $limit Number of ads to return
 * @return array Array of ads
 */
function getFeaturedAds($limit = 6) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name, u.username, u.first_name, u.last_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'active' 
            ORDER BY a.created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching featured ads: " . $e->getMessage());
        return [];
    }
}

/**
 * Get ads by category
 * 
 * @param int $categoryId Category ID
 * @param int $limit Number of ads to return
 * @return array Array of ads
 */
function getAdsByCategory($categoryId, $limit = 10) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name, u.username, u.first_name, u.last_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'active' AND a.category_id = ? 
            ORDER BY a.created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$categoryId, $limit]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching ads by category: " . $e->getMessage());
        return [];
    }
}

/**
 * Get related ads (same category, excluding current ad)
 * 
 * @param int $adId Current ad ID to exclude
 * @param int $categoryId Category ID
 * @param int $limit Number of ads to return
 * @return array Array of ads
 */
function getRelatedAds($adId, $categoryId, $limit = 4) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            SELECT a.*, c.name as category_name 
            FROM ads a 
            JOIN categories c ON a.category_id = c.id 
            WHERE a.status = 'active' AND a.category_id = ? AND a.id != ? 
            ORDER BY a.created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute([$categoryId, $adId, $limit]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching related ads: " . $e->getMessage());
        return [];
    }
}

/**
 * Get category statistics
 * 
 * @return array Array of categories with ad counts
 */
function getCategoryStats() {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->query("
            SELECT c.*, COUNT(a.id) as ad_count 
            FROM categories c 
            LEFT JOIN ads a ON c.id = a.category_id AND a.status = 'active' 
            WHERE c.is_active = 1 
            GROUP BY c.id 
            ORDER BY c.name
        ");
        
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching category stats: " . $e->getMessage());
        return [];
    }
}
?>

