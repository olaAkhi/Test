<?php

namespace Core;

class Csrf {
    private static $tokenName = 'csrf_token';

    /**
     * Generates a CSRF token and stores it in the session.
     * If a token already exists, it returns that one unless forced to regenerate.
     * @param bool $forceRegenerate If true, a new token is generated even if one exists.
     * @return string The CSRF token.
     */
    public static functiongenerateToken(bool $forceRegenerate = false): string {
        if (!$forceRegenerate && isset($_SESSION[self::$tokenName])) {
            return $_SESSION[self::$tokenName];
        }

        try {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::$tokenName] = $token;
            return $token;
        } catch (\Exception $e) {
            // Fallback for environments where random_bytes might fail (highly unlikely for PHP 7+)
            // For PHP < 7, consider openssl_random_pseudo_bytes or mcrypt_create_iv
            // This is a basic fallback, ensure your environment supports random_bytes.
            $token = md5(uniqid(rand(), true) . microtime(true));
            $_SESSION[self::$tokenName] = $token;
            error_log("CSRF Token generation used fallback: " . $e->getMessage());
            return $token;
        }
    }

    /**
     * Validates a submitted CSRF token against the one stored in the session.
     * To prevent timing attacks, use hash_equals.
     * @param string $submittedToken The token submitted with the form.
     * @return bool True if valid, false otherwise.
     */
    public static functionvalidateToken(string $submittedToken): bool {
        if (!isset($_SESSION[self::$tokenName])) {
            // No token in session, so validation fails.
            return false;
        }

        $sessionToken = $_SESSION[self::$tokenName];

        // It's good practice to unset the token after first use for some CSRF strategies (Synchronizer Token Pattern variants)
        // For simplicity here, we can keep it same for session lifetime or regenerate per request.
        // If unsetting, ensure generateToken is called on every form load.
        // unset($_SESSION[self::$tokenName]); // Optional: uncomment for one-time tokens

        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Returns the HTML hidden input field for the CSRF token.
     * @return string HTML input field.
     */
    public static functiongetInputField(): string {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Call this at the beginning of POST request handlers to check CSRF token.
     * Exits script with error if validation fails.
     */
    public static functioncheckPostToken(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedToken = $_POST[self::$tokenName] ?? '';
            if (!self::validateToken($submittedToken)) {
                // Log the CSRF attempt for security monitoring
                error_log("CSRF token validation failed. IP: {$_SERVER['REMOTE_ADDR']}, Request URI: {$_SERVER['REQUEST_URI']}");
                $_SESSION['error_message'] = 'Invalid security token. Please try submitting the form again.';
                // Redirect to a safe page, or the previous page if possible.
                // Avoid exiting with die() in a real app, handle redirect gracefully.
                // For simplicity in this project, we might redirect to home or the current action page (GET).
                $redirectAction = $_GET['action'] ?? 'home';
                header('Location: index.php?action=' . $redirectAction);
                exit;
            }
        }
    }
}
?>
