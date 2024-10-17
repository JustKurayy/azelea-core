<?php
namespace Azelea\Core\Standard;
use Azelea\Core\Core;

/**
 * The AzeleaRouter handles all page loading,
 * redirects the urls to its correspondig pages and
 * handles the controllers.
 */
class Router {
    private $routes = [];

    public function addRoute($method, $path, $handler, $args = []) {
        array_push($this->routes, [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'args' => $args
        ]);
    }

    public function load() {
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");

        try {
            foreach ($this->routes as $route) {
                if ($route['path'] === $_SERVER['REQUEST_URI']) { //$route['method'] === $_SERVER["REQUEST_METHOD"] && 
                    $rt = explode("::", $route['handler']);
                    $className = "Azelea\\Core\\" . $rt[0];
                    $class = new $className;
                    $func = $rt[1];
                    $args = $route['args'];
                    return (count($args) === 0) ? $class->$func() : $class->$func($args);
                }
            }
    
            throw new \Exception("Invalid Route");
        } catch (\Exception $e) {
            Core::error($e);
        }
    }
}
