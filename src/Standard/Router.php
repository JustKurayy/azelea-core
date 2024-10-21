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

    /**
     * Adds a route to the backlog.
     * `$handler` can be used like 'CustomController::method'
     * 
     * @param array $method All HTTP methods this route is allowed to use
     * @param string $path The route URL
     * @param mixed $handler Name of the custom controller and the method within
     * @return int The amount of routes stored
     */
    public function addRoute(array $method, string $path, $handler) {
        return array_push($this->routes, [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ]);
    }

    /**
     * Loads the correct route from the backlog
     */
    public function load() {
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        
        foreach ($this->routes as $route) {
            if (in_array($_SERVER["REQUEST_METHOD"], $route['method']) && $route['path'] === $_SERVER['REQUEST_URI']) {
                $routeParams = explode("::", $route['handler']);
                $className = "Azelea\\Core\\" . $routeParams[0];
                $classMethod = $routeParams[1];
                $class = $this->injectDependencies($className, $classMethod);
                return $class;
            }
        }
        return ($_ENV['APP'] == "prod" || $_ENV['APP'] == "production") ? header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,404) : throw new \Exception("Invalid Route");
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
    private function injectDependencies($class, $methodName) {
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
       
        try {
            $instance = $reflector->newInstance();
            return $method->invokeArgs($instance, $dependencies);
        } catch (\Exception $e) {
            Core::error($e);
        }
    }   
}
