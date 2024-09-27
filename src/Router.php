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
        $routes[] = array('method' => $method, 'path' => $path, 'handler' => $handler);
        return $this->dispatch("GET", $_SERVER['REQUEST_URI'], $routes, $args);
    }

    private function dispatch($requestMethod, $requestPath, $routes, $args = []) {
        foreach ($routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestPath) {
                $rt = explode("::", $route['handler']); //apparently the namespace wasn't good enough
                $className = "Azelea\\Core\\".$rt[0];
                $class = new $className;
                $func = $rt[1];
                $call = (count($args) === 0) ? $class->$func() : $class->$func($args);
                return;
            }
        }
        //commented because it is unchecked, resulting in it always returning 404
        return; //http_response_code(404);
    }
}
