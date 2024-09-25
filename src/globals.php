<?php
/**
 * I don't know if it is smart to create global functions.
 * I don't care, I'm doing it anyways.
 * Here are the global functions for AzeleaCore
 */

/**
 * Debugs given param.
 * @param mixed $item
 */
function dd($item) {
    var_dump($item);
    die();
}
