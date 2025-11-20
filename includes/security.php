<?php
/**
 * Security Utilities
 * 
 * This file contains security-related functions for input validation,
 * sanitization, and protection against common web vulnerabilities.
 */

/**
 * Sanitize input data
 * 
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    if (is_string($data)) {
        // Remove null bytes
        $data = str_replace("\0", '', $data);
        
        // Trim whitespace
        $data = trim($data);
        
        // Remove slashes if magic quotes is on (legacy)
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $data = stripslashes($data);
        }
        
        return $data;
    }
    
    return $data;
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid length (7-15 digits)
    return strlen($phone) >= 7 && strlen($phone) <= 15;
}

/**
 * Validate URL
 * 
 * @param string $url URL to validate
 * @return bool True if valid, false otherwise
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Check password strength
 * 
 * @param string $password Password to check
 * @return array Result with strength score and feedback
 */
function checkPasswordStrength($password) {
    $score = 0;
    $feedback = [];
    
    // Length check
    if (strlen($password) >= 8) {
        $score += 2;
    } else {
        $feedback[] = 'Password should be at least 8 characters long';
    }
    
    // Uppercase letter
    if (preg_match('/[A-Z]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = 'Include at least one uppercase letter';
    }
    
    // Lowercase letter
    if (preg_match('/[a-z]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = 'Include at least one lowercase letter';
    }
    
    // Number
    if (preg_match('/[0-9]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = 'Include at least one number';
    }
    
    // Special character
    if (preg_match('/[^A-Za-z0-9]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = 'Include at least one special character';
    }
    
    // Determine strength level
    if ($score >= 5) {
        $strength = 'strong';
    } elseif ($score >= 3) {
        $strength = 'medium';
    } else {
        $strength = 'weak';
    }
    
    return [
        'score' => $score,
        'strength' => $strength,
        'feedback' => $feedback
    ];
}

/**
 * Rate limiting implementation
 * 
 * @param string $action Action being performed
 * @param string $identifier User identifier (IP, user ID, etc.)
 * @param int $maxAttempts Maximum attempts allowed
 * @param int $timeWindow Time window in seconds
 * @return bool True if action is allowed, false if rate limited
 */
function checkRateLimit($action, $identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = "rate_limit_{$action}_{$identifier}";
    
    // Use session for simple rate limiting (in production, use Redis or database)
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    
    // Clean old entries
    foreach ($_SESSION['rate_limits'] as $k => $data) {
        if ($data['expires'] < $now) {
            unset($_SESSION['rate_limits'][$k]);
        }
    }
    
    // Check current rate limit
    if (isset($_SESSION['rate_limits'][$key])) {
        $data = $_SESSION['rate_limits'][$key];
        
        if ($data['attempts'] >= $maxAttempts) {
            return false; // Rate limited
        }
        
        $_SESSION['rate_limits'][$key]['attempts']++;
    } else {
        $_SESSION['rate_limits'][$key] = [
            'attempts' => 1,
            'expires' => $now + $timeWindow
        ];
    }
    
    return true; // Action allowed
}

/**
 * Log security events
 * 
 * @param string $event Event description
 * @param string $level Severity level (info, warning, error)
 * @param array $context Additional context
 */
function logSecurityEvent($event, $level = 'info', $context = []) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'level' => $level,
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'context' => $context
    ];
    
    $logFile = __DIR__ . '/../logs/security.log';
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Write log entry
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Detect and prevent SQL injection attempts
 * 
 * @param string $input Input to check
 * @return bool True if suspicious, false otherwise
 */
function detectSQLInjection($input) {
    $patterns = [
        '/(\bunion\b.*\bselect\b)/i',
        '/(\bselect\b.*\bfrom\b)/i',
        '/(\binsert\b.*\binto\b)/i',
        '/(\bupdate\b.*\bset\b)/i',
        '/(\bdelete\b.*\bfrom\b)/i',
        '/(\bdrop\b.*\btable\b)/i',
        '/(\bor\b.*=.*)/i',
        '/(\band\b.*=.*)/i',
        '/(\'.*or.*\'.*=.*\')/i',
        '/(\".*or.*\".*=.*\")/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('SQL injection attempt detected', 'warning', ['input' => $input]);
            return true;
        }
    }
    
    return false;
}

/**
 * Detect and prevent XSS attempts
 * 
 * @param string $input Input to check
 * @return bool True if suspicious, false otherwise
 */
function detectXSS($input) {
    $patterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
        '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            logSecurityEvent('XSS attempt detected', 'warning', ['input' => $input]);
            return true;
        }
    }
    
    return false;
}

/**
 * Validate file upload
 * 
 * @param array $file $_FILES array element
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array Validation result
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
        $errors[] = 'File type not allowed';
    }
    
    // Check for executable files
    $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (in_array($extension, $dangerousExtensions)) {
        $errors[] = 'Executable files are not allowed';
        logSecurityEvent('Attempt to upload executable file', 'warning', ['filename' => $file['name']]);
    }
    
    // Additional security checks for images
    if (strpos($mimeType, 'image/') === 0) {
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Invalid image file';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'mime_type' => $mimeType
    ];
}

/**
 * Generate secure random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash sensitive data
 * 
 * @param string $data Data to hash
 * @param string $salt Optional salt
 * @return string Hashed data
 */
function hashSensitiveData($data, $salt = '') {
    return hash('sha256', $data . $salt);
}

/**
 * Validate and sanitize user input for ads
 * 
 * @param array $data Input data
 * @return array Validation result
 */
function validateAdData($data) {
    $errors = [];
    $sanitized = [];
    
    // Title validation
    if (empty($data['title'])) {
        $errors['title'] = 'Title is required';
    } else {
        $title = sanitizeInput($data['title']);
        if (strlen($title) < 3 || strlen($title) > 200) {
            $errors['title'] = 'Title must be between 3 and 200 characters';
        } elseif (detectXSS($title)) {
            $errors['title'] = 'Invalid characters in title';
        } else {
            $sanitized['title'] = $title;
        }
    }
    
    // Description validation
    if (empty($data['description'])) {
        $errors['description'] = 'Description is required';
    } else {
        $description = sanitizeInput($data['description']);
        if (strlen($description) < 10) {
            $errors['description'] = 'Description must be at least 10 characters';
        } elseif (detectXSS($description)) {
            $errors['description'] = 'Invalid characters in description';
        } else {
            $sanitized['description'] = $description;
        }
    }
    
    // Category validation
    if (empty($data['category_id']) || !is_numeric($data['category_id'])) {
        $errors['category_id'] = 'Valid category is required';
    } else {
        $sanitized['category_id'] = (int)$data['category_id'];
    }
    
    // Price validation
    if (!empty($data['price'])) {
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            $errors['price'] = 'Price must be a valid positive number';
        } else {
            $sanitized['price'] = (float)$data['price'];
        }
    }
    
    // Location validation
    if (!empty($data['location'])) {
        $location = sanitizeInput($data['location']);
        if (strlen($location) > 100) {
            $errors['location'] = 'Location must be less than 100 characters';
        } elseif (detectXSS($location)) {
            $errors['location'] = 'Invalid characters in location';
        } else {
            $sanitized['location'] = $location;
        }
    }
    
    // Email validation
    if (!empty($data['contact_email'])) {
        $email = sanitizeInput($data['contact_email']);
        if (!validateEmail($email)) {
            $errors['contact_email'] = 'Invalid email address';
        } else {
            $sanitized['contact_email'] = $email;
        }
    }
    
    // Phone validation
    if (!empty($data['contact_phone'])) {
        $phone = sanitizeInput($data['contact_phone']);
        if (!validatePhone($phone)) {
            $errors['contact_phone'] = 'Invalid phone number';
        } else {
            $sanitized['contact_phone'] = $phone;
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Check if request is from a bot
 * 
 * @return bool True if likely a bot, false otherwise
 */
function isBot() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $botPatterns = [
        '/bot/i',
        '/crawler/i',
        '/spider/i',
        '/scraper/i',
        '/curl/i',
        '/wget/i'
    ];
    
    foreach ($botPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get client IP address
 * 
 * @return string Client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}
?>

