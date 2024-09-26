<?php
namespace Azelea\Core;

/**
 * FaultManager is supposed to make
 * custom error screens in dev mode,
 * and redirect you to its corresponding
 * page in production mode.
 */
class Fault {
    public function error($error) {
        var_dump($error);
        die();
    }
}

$faultManager = new Fault();