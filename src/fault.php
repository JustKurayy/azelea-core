<?php
namespace Azelea;

/**
 * FaultManager is supposed to make
 * custom error screens in dev mode,
 * and redirect you to its corresponding
 * page in production mode.
 */
class FaultManager {
    public function error($error) {
        echo $error;
    }
}

$faultManager = new FaultManager();