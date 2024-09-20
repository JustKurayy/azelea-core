<?php

class FaultManager {
    public function error($error) {
        echo $error;
    }
}

$faultManager = new FaultManager();