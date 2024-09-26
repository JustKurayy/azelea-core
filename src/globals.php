<?php
namespace Azelea;

/**
 * Debugs given param.
 * @param mixed $item
 */
function dd($item) {
    var_dump($item);
    die();
}
