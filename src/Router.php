<?php
namespace Azelea\Core;

/**
 * The AzeleaRouter handles all page loading,
 * redirects the urls to its correspondig pages and
 * handles the controllers.
 * Should be adjusted to load arguments for the controllers
 * dynamically
 */
class Router {
    public function addRoute($method, $path, $handler, $args = []) {
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        try {
            if ($method == $_SERVER["REQUEST_METHOD"] && $path == $_SERVER['REQUEST_URI']) {
                $rt = explode("::", $handler);
                $className = "Azelea\\Core\\" . $rt[0];
                $class = new $className;
                $func = $rt[1];
                $call = (count($args) === 0) ? $class->$func() : $class->$func($args);
                return;
            } else {
                throw new \Exception("Invalid Route");
                return;
            }
        } catch (\Exception $e) {
            return Core::error($e);
        }
    }
}
