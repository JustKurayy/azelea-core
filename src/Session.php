<?php
namespace Azelea\Core;

class Session {
    public function __construct() {
        session_start();
    }
}
