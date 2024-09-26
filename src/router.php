<?php
namespace Azelea;

/**
 * The AzeleaRouter handles all page loading,
 * redirects the urls to its correspondig pages and
 * handles the controllers.
 * Should be adjusted to load arguments for the controllers
 * dynamically
 */
class Router {
    public function addRoute($method, $path, $handler, $args) {
        $routes[] = array('method' => $method, 'path' => $path, 'handler' => $handler);
        return $this->dispatch("GET", $_SERVER['REQUEST_URI'], $routes, $args);
    }

    private function dispatch($requestMethod, $requestPath, $routes, $args) {
        foreach ($routes as $route) {
            if ($route['method'] === $requestMethod && $route['path'] === $requestPath) {
                $rt = explode("::", $route['handler']);
                $class = new $rt[0];
                $func = $rt[1];
                if ($args != null) {
                    $class->$func($args);
                } else {
                    $class->$func();
                }
                return;
            }
        }
        //commented because it is unchecked, resulting in it always returning 404
        return; //http_response_code(404);
    }
}
