<?php

namespace Azelea\Core\Authentication;

use Azelea\Core\Database\DatabaseManager;
use Azelea\Core\Session;

/**
 * Azelea Auth is still work in progress.
 * Security is not optimal yet!
 */
class Auth {
    /**
     * Logs the user in and stores it in the session.
     * @param string $class The name of the class
     * @param mixed $form The form where the POST data is stored
     * @return object|false
     */
    static public function attempt(string $class, $form) {
        $db = new DatabaseManager();
        $config = Auth::getAuthDetails();
        $id = $form->getData($config);
        $user = $db->getModel($class, $id);
        if (password_verify($form->getData("password"), $user->getPassword())) {
            $token = $db->generateToken();
            $userId = $user->getId();
            //there should also be a section to check if this id already has a token.
            //if so, that token should be revoked and this one should be used instead.
            //in config, there should be a limit of tokens a single ID can have.
            //like five tokens, meaning that id can login to 5 different browsers before 
            //the oldest token starts to get revoked.
            $db->addSql("INSERT INTO tokens (uuid, user_id) VALUES ('$token', '$userId')");
            $db->push();
            Session::setSessionKey("user_id", $token);
            return $user;
        } else {
            Session::addFlash("Email and password do not match", "danger");
            return false;
        }
    }

    /**
     * Authenticates the user
     * @deprecated Use Auth::attempt() instead
     */
    static public function login(string $class, $form) {
        Auth::attempt($class, $form);
    }

    /**
     * Retrieves the authenticated user
     */
    static public function retrieve() {

    }

    static public function getId() {

    }

    /**
     * Returns the user login identifier from the config file.
     * @return string The identifier type
     */
    static public function getAuthDetails() {
        return $_ENV['AUTH_ID'];
    }

    /**
     * Capture logged user
     */
    static public function captureInformation() {

    }
}
