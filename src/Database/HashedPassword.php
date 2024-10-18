<?php
namespace Azelea\Database;

/**
 * Hashes the password?
 */
#[HashedPassword]
class HashedPassword {
    private string $password;
    public function __construct(string $password) {
        $this->password = $password;
    }

    public function hashPassword() {
        $hash = password_hash($this->password, PASSWORD_DEFAULT);
        $success = ($hash) ? $hash : false;
        return $success;
    }
}
