<?php
namespace Azelea\Database;

/**
 * Hashes the password?
 */
#[HashedPassword]
class HashedPassword {
    public function __construct(string $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $success = ($hash) ? $hash : false;
        return $success;
    }
}
