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
                    $routeParams = explode("::", $route['handler']);
                    $className = "Azelea\\Core\\" . $routeParams[0];
                    $classMethod = $routeParams[1];
                    $class = $this->injectDependencies($className, $classMethod, $route['args']);
                    return $class;
                }
            }
    
            throw new \Exception("Invalid Route");
        } catch (\Exception $e) {
            Core::error($e);
        }
    }

    /**
     * Injects dependencies of custom controller methods.
     * Only works with classes now.
     * 
     * @param object|mixed $class Name of the custom controller class
     * @param string $methodName Name of the method in the custom controller
     * @param array $userArgs Is passed through custom routes (routes.php)
     * @return object
     */
    private function injectDependencies($class, $methodName, array $userArgs = []) {
        $reflector = new \ReflectionClass($class);
        $method = $reflector->getMethod($methodName);
        $parameters = $method->getParameters();
        $dependencies = [];
    
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
    
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                if (class_exists($className)) array_push($dependencies, new $className());
            } else {
                $dependencies[] = null;
            }
        }

        $instance = $reflector->newInstance();
        return $method->invokeArgs($instance, $dependencies);
    }    
}
