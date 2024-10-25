<?php
namespace Azelea\Core;

/**
 * The Session Manager for the Framework.
 * `new Session()` can be called multiple times.
 */
class Session {
    public function __construct() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->startSession();
        }

        // Remove leftover flashes
        if(isset($_SESSION['flashes'])) {
            $_SESSION['flashes'] = null;
            unset($_SESSION['flashes']);
        }

        if ($this->hasUserId()) {
            // Core::dd(self::getSessionKey("user_id"));
        }
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

    /**
     * @deprecated
     */
    public function set($key, $value) {
        $_SESSION[$key] = $this->sanitize($value);
    }

    static public function setSessionKey($key, $value) {
        $_SESSION[$key] = self::dataSanitize($value);
    }

    /**
     * @deprecated
     */
    public function get($key) {
        return isset($_SESSION[$key]) ? $this->sanitize($_SESSION[$key]) : null;
    }

    static public function getSessionKey($key) {
        return isset($_SESSION[$key]) ? self::dataSanitize($_SESSION[$key]) : null;
    }

    static public function addFlash(string $message, string $type) {
        $flashes = self::getSessionKey("flashes");
        if ($flashes === null) {
            self::setSessionKey("flashes", [[
                    'message'=> $message,
                    'type' => $type
                ]]);
        } else {
            return array_push($flashes, [
                'message'=> $message,
                'type' => $type
            ]);
        }
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    private function getUserId() {
        return self::getSessionKey('user_id');
    }

    /**
     * @deprecated
     */
    public function setUserId($userId) {
        $this->set('user_id', $userId);
    }

    private function hasUserId(): bool {
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
     * @deprecated
     */
    private function sanitize($data) {
        if (is_string($data)) return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    /**
     * Sanitize data to prevent XSS and other injection attacks
     */
    static private function dataSanitize($data) {
        if (is_string($data)) return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}
