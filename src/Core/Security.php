<?php
namespace App\Core;

/**
 * Security Helper Class
 * Provides protection against XSS, SQL Injection, and CSRF attacks
 */
class Security {
    // CSRF token field name (must match form field names)
    const TOKEN_FIELD = '_csrf_token';

    /**
     * CSRF Protection - Get or generate CSRF token
     */
    public static function getCSRFToken() {
        return Csrf::getToken();
    }

    /**
     * CSRF Protection - Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return Csrf::verify($token);
    }

    /**
     * XSS Protection - Sanitize output (HTML Escape)
     * Use in all views when displaying user input
     * 
     * @param string|null $value Value to escape
     * @param string $flags ENT_QUOTES | ENT_COMPAT | ENT_NOQUOTES
     * @return string Escaped HTML
     */
    public static function escapeHtml($value, $flags = ENT_QUOTES) {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, $flags, 'UTF-8');
    }

    /**
     * XSS Protection - Sanitize input (Remove dangerous HTML tags)
     * Use before storing user input in database
     * 
     * @param string|null $value Value to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeInput($value) {
        if ($value === null) {
            return '';
        }
        $value = trim((string)$value);
        $value = stripslashes($value); // Remove slashes if magic_quotes is on
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * XSS Protection - Strip all HTML/PHP tags
     * Use for strict sanitization
     * 
     * @param string|null $value Value to strip
     * @return string Stripped value
     */
    public static function stripTags($value) {
        if ($value === null) {
            return '';
        }
        $value = strip_tags((string)$value);
        return trim($value);
    }

    /**
     * Email validation and sanitization
     * 
     * @param string $email Email to validate
     * @return string|false Valid email or false
     */
    public static function sanitizeEmail($email) {
        $email = trim((string)$email);
        return filter_var($email, FILTER_SANITIZE_EMAIL) !== false 
            ? filter_var($email, FILTER_VALIDATE_EMAIL)
            : false;
    }

    /**
     * URL sanitization
     * 
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitizeUrl($url) {
        $url = trim((string)$url);
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Integer validation and sanitization
     * 
     * @param mixed $value Value to validate
     * @return int|false Valid integer or false
     */
    public static function sanitizeInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false 
            ? (int)filter_var($value, FILTER_VALIDATE_INT)
            : false;
    }

    /**
     * Float validation and sanitization
     * 
     * @param mixed $value Value to validate
     * @return float|false Valid float or false
     */
    public static function sanitizeFloat($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false 
            ? (float)filter_var($value, FILTER_VALIDATE_FLOAT)
            : false;
    }

    /**
     * SQL Injection Protection - Sanitize for LIKE queries
     * Escapes special characters for LIKE clause
     * 
     * @param string $value Value to escape for LIKE
     * @return string Escaped value
     */
    public static function escapeLikeString($value) {
        // Escape special characters used in LIKE queries
        $value = str_replace(['%', '_'], ['\%', '\_'], (string)$value);
        return $value;
    }

    /**
     * Check if request is AJAX/XmlHttpRequest
     * 
     * @return bool True if AJAX request
     */
    public static function isAjaxRequest() {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        );
    }

    /**
     * Check if request method is POST
     * 
     * @return bool True if POST request
     */
    public static function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request method is GET
     * 
     * @return bool True if GET request
     */
    public static function isGetRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Rate limiting - Basic implementation
     * Store in $_SESSION
     * 
     * @param string $identifier Unique identifier (e.g., IP, user_id)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed, false if limit exceeded
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 60) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $key = 'rate_limit_' . md5($identifier);
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => $now + $timeWindow];
            return true;
        }

        // Check if time window has passed
        if ($now > $_SESSION[$key]['reset_time']) {
            $_SESSION[$key] = ['count' => 0, 'reset_time' => $now + $timeWindow];
            return true;
        }

        // Increment counter
        $_SESSION[$key]['count']++;

        return $_SESSION[$key]['count'] <= $maxAttempts;
    }

    /**
     * Get client IP address (considering proxies)
     * 
     * @return string Client IP address
     */
    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs in X-Forwarded-For
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }

        // Validate IP
        return filter_var($ip, FILTER_VALIDATE_IP) ?: 'INVALID';
    }

    /**
     * Hash password using bcrypt
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password Password to verify
     * @param string $hash Hash to verify against
     * @return bool True if password matches
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Set secure headers for XSS and other attacks protection
     * Call this in your layout/base template
     */
    public static function setSecureHeaders() {
        // Prevent XSS
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');

        // Content Security Policy - Allow Font Awesome & external resources
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
               "style-src 'self' 'unsafe-inline' https:; " .
               "font-src 'self' data: https: blob:; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https:; " .
               "frame-src 'self' https://accounts.google.com; " .
               "media-src 'self' https:;";
        
        header("Content-Security-Policy: $csp");

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable HSTS (only on HTTPS)
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Sanitize database query for common injection patterns
     * NOTE: This is a supplementary check only - ALWAYS use prepared statements
     * 
     * @param string $query Query to check
     * @return bool True if query looks suspicious
     */
    public static function hasInjectionPatterns($query) {
        $dangerous_patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bor\b.*=.*)/i',
            '/(\bdrop\b)/i',
            '/(\binsert\b)/i',
            '/(\bupdate\b)/i',
            '/(\bdelete\b)/i',
            '/(-{2,})/i', // SQL comment
            '/\/\*/i',    // SQL comment
        ];

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }
}
?>
