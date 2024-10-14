<?php
namespace Azelea\Core;

/**
 * The Session Manager for the Framework.
 * Has built in functions for security.
 */
class Session {
    public function __construct() {
        $this->startSession();
    }

    /**
     * Starts the session with secure settings
     */
    private function startSession() {
        session_start([
            'cookie_lifetime' => 0, // Session lasts until the browser is closed
            'cookie_secure' => true, // Ensure the cookie is sent over secure connections
            'cookie_httponly' => true, // Prevent JavaScript access to session cookie
            'cookie_samesite' => 'Strict', // Prevent CSRF attacks
        ]);

        // Regenerate session ID to prevent session fixation
        if (empty($_SESSION['initialized'])) {
            session_regenerate_id(true); // true to delete the old session
            $_SESSION['initialized'] = true;
        }
    }

    public function set($key, $value) {
        $_SESSION[$key] = $this->sanitize($value);
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $this->sanitize($_SESSION[$key]) : null;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function getUserId() {
        return $this->get('user_id');
    }

    public function setUserId($userId) {
        $this->set('user_id', $userId);
    }

    public function hasUserId(): bool {
        return isset($_SESSION["user_id"]);
    }

    /**
     * Destroys the session and deletes the data.
     */
    public function destroy() {
        session_unset();
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Sanitize data to prevent XSS and other injection attacks
     */
    private function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
