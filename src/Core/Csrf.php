<?php
namespace App\Core;

/**
 * CSRF (Cross-Site Request Forgery) Protection
 * Generates and validates CSRF tokens to prevent unauthorized requests
 */
class Csrf {
    
    // Token field name in forms
    const TOKEN_FIELD = '_csrf_token';
    
    // Session key for storing token
    const SESSION_KEY = 'csrf_token';
    
    // Token expiration time (1 hour)
    const TOKEN_EXPIRY = 3600;

    /**
     * Get or generate CSRF token
     * Returns the current token if valid, generates new one if expired/missing
     * 
     * @return string CSRF token
     */
    public static function getToken() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if token exists and is not expired
        if (!empty($_SESSION[self::SESSION_KEY])) {
            $tokenData = $_SESSION[self::SESSION_KEY];
            
            // Verify token hasn't expired
            if (isset($tokenData['expires']) && $tokenData['expires'] > time()) {
                return $tokenData['token'];
            }
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = [
            'token' => $token,
            'expires' => time() + self::TOKEN_EXPIRY,
            'created_at' => time()
        ];

        return $token;
    }

    /**
     * Verify CSRF token from request
     * Uses timing-safe comparison
     * 
     * @param string|null $requestToken Token to verify (from POST/AJAX)
     * @return bool True if token is valid
     */
    public static function verify($requestToken = null) {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Token not provided
        if (empty($requestToken)) {
            // Try to get from POST or request header
            $requestToken = $_POST[self::TOKEN_FIELD] ?? 
                           $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 
                           null;
        }

        // No token in request
        if (empty($requestToken)) {
            return false;
        }

        // No token in session
        if (empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        $tokenData = $_SESSION[self::SESSION_KEY];
        $sessionToken = $tokenData['token'] ?? null;

        // Token expired
        if (isset($tokenData['expires']) && $tokenData['expires'] <= time()) {
            unset($_SESSION[self::SESSION_KEY]);
            return false;
        }

        // Token doesn't match (use timing-safe comparison)
        if ($sessionToken === null || !hash_equals($sessionToken, $requestToken)) {
            return false;
        }

        return true;
    }

    /**
     * Regenerate CSRF token
     * Use after login/logout for security
     */
    public static function regenerate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION[self::SESSION_KEY]);
        return self::getToken();
    }

    /**
     * Get HTML hidden input for forms
     * Easy way to include CSRF token in HTML forms
     * 
     * @return string HTML hidden input element
     */
    public static function getHtmlInput() {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s" />',
            htmlspecialchars(self::TOKEN_FIELD, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Check CSRF token and throw exception if invalid
     * Use this for strict validation
     * 
     * @param string|null $requestToken Token to verify
     * @throws \Exception If token is invalid
     */
    public static function validateOrThrow($requestToken = null) {
        if (!self::verify($requestToken)) {
            http_response_code(403);
            throw new \Exception('CSRF token validation failed');
        }
    }
}