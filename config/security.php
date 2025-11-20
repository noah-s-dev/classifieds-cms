<?php
/**
 * Security Configuration
 * 
 * This file contains security-related configuration settings
 * for the classifieds CMS application.
 */

// Security headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";
    header("Content-Security-Policy: $csp");
    
    // HTTPS enforcement (uncomment in production with HTTPS)
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Session security configuration
function configureSecureSessions() {
    // Session cookie settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    
    // Session regeneration
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// File upload security settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
]);

// Rate limiting settings
define('LOGIN_RATE_LIMIT', 5); // Max login attempts
define('LOGIN_RATE_WINDOW', 900); // 15 minutes
define('REGISTRATION_RATE_LIMIT', 3); // Max registrations per IP
define('REGISTRATION_RATE_WINDOW', 3600); // 1 hour
define('AD_POST_RATE_LIMIT', 10); // Max ads per user per day
define('AD_POST_RATE_WINDOW', 86400); // 24 hours

// Password policy
define('MIN_PASSWORD_LENGTH', 8);
define('REQUIRE_UPPERCASE', true);
define('REQUIRE_LOWERCASE', true);
define('REQUIRE_NUMBERS', true);
define('REQUIRE_SPECIAL_CHARS', false);

// Input validation settings
define('MAX_TITLE_LENGTH', 200);
define('MIN_TITLE_LENGTH', 3);
define('MIN_DESCRIPTION_LENGTH', 10);
define('MAX_DESCRIPTION_LENGTH', 5000);
define('MAX_LOCATION_LENGTH', 100);

// Logging settings
define('LOG_SECURITY_EVENTS', true);
define('LOG_FAILED_LOGINS', true);
define('LOG_SUSPICIOUS_ACTIVITY', true);

// IP blocking (simple implementation)
$blocked_ips = [
    // Add IPs to block here
    // '192.168.1.100',
];

function isIPBlocked($ip) {
    global $blocked_ips;
    return in_array($ip, $blocked_ips);
}

// Initialize security
function initializeSecurity() {
    // Set security headers
    setSecurityHeaders();
    
    // Configure secure sessions
    configureSecureSessions();
    
    // Check for blocked IPs
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (isIPBlocked($clientIP)) {
        http_response_code(403);
        die('Access denied');
    }
    
    // Basic bot detection and rate limiting
    if (isBot() && !in_array($_SERVER['REQUEST_URI'] ?? '', ['/robots.txt', '/sitemap.xml'])) {
        // Allow bots for specific pages only
        http_response_code(429);
        die('Too many requests');
    }
}

// Helper function to check if running in development mode
function isDevelopmentMode() {
    return defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true;
}

// Error reporting based on environment
if (isDevelopmentMode()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// Initialize security when this file is included
initializeSecurity();
?>

