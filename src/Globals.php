<?php

/**
 * This part of the Core
 * contains global functions 
 * that can be called from out 
 * of the skeleton anywhere.
 * Incase you want to debug the 
 * Core Library itself,
 * you will still need to 
 * use Core::dd();
 * 
 * These global functions can only
 * be used inside the skeleton itself.
 */

namespace Azelea\Core;

/**
 * Declares the Debug Data global
 */
if (!function_exists("dd")) {
    function dd($item)
    {
        Core::dd($item);
    }
}
