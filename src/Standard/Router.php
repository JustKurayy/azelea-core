<?php
namespace Azelea\Core\Standard;

use Azelea\Core\Core;

/**
 * The AzeleaRouter handles all page loading,
 * redirects the URLs to their corresponding pages, and
 * manages the controllers.
 */
class Router {
    private $routes = [];

    /**
     * Adds a route to the backlog.
     * 
     * @param array $method Allowed HTTP methods for this route
     * @param string $path The route URL
     * @param string $handler The controller method in the format 'CustomController::method'
     * @return int The number of routes stored
     */
    public function addRoute(array $method, string $path, string $handler): int {
        $pathRegex = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        $pathRegex = str_replace('/', '\/', $pathRegex);
        $pathRegex = '/^' . $pathRegex . '$/';
        
        return array_push($this->routes, [
            'method' => $method,
            'path' => $pathRegex,
            'handler' => $handler,
            'params' => $path
        ]);
    }

    /**
     * Loads the correct route from the backlog and invokes the corresponding handler.
     */
    public function load() {
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
    
        foreach ($this->routes as $route) {
            if ($this->isRouteMatched($route)) {
                return $this->handleRoute($route);
            }
        }
    
        $this->handleNotFound();
    }    

    /**
     * Checks if the current request matches the given route.
     * 
     * @param array $route The route to check
     * @return bool True if the route matches, false otherwise
     */
    private function isRouteMatched(array $route): bool {
        return in_array($_SERVER["REQUEST_METHOD"], $route['method']) && 
               preg_match($route['path'], $_SERVER['REQUEST_URI'], $matches);
    }

    /**
     * Handles the matched route by invoking the corresponding method.
     * 
     * @param array $route The matched route
     * @return mixed The result of the controller method
     */
    private function handleRoute(array $route) {
        preg_match($route['path'], $_SERVER['REQUEST_URI'], $matches);
        array_shift($matches); // Remove the original from array
    
        // Creates an array with named parameters
        $params = [];
        preg_match_all('/\{(\w+)\}/', $route['params'], $paramNames);
        
        foreach ($paramNames[1] as $index => $paramName) {
            $params[$paramName] = $matches[$index];
        }
    
        [$className, $classMethod] = explode("::", $route['handler']);
        $className = "Azelea\\Core\\" . $className;
    
        return $this->injectDependencies($className, $classMethod, $params);
    }

    /**
     * Handles a 404 Not Found error.
     * Returns an error in dev mode.
     */
    private function handleNotFound() {
        if ($_ENV['APP'] === "prod" || $_ENV['APP'] === "production") {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
        } else {
            throw new \Exception("Invalid Route");
        }
    }

    /**
     * Injects dependencies into the controller method.
     * 
     * @param string $class The custom controller class name
     * @param string $methodName The method in the controller
     * @param array $params URL parameter information
     * @return mixed
     */
    private function injectDependencies(string $class, string $methodName, array $params = []) {
        $reflector = new \ReflectionClass($class);
        $method = $reflector->getMethod($methodName);
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $params);
        }

        try {
            $instance = $reflector->newInstance();
            return $method->invokeArgs($instance, $dependencies);
        } catch (\Exception $e) {
            Core::error($e);
        }
    }

    /**
     * Resolves a single parameter for dependency injection.
     * 
     * @param \ReflectionParameter $parameter The parameter to resolve
     * @param array $params The URL parameters
     * @return mixed The resolved parameter value
     */
    private function resolveParameter(\ReflectionParameter $parameter, array $params) {
        $type = $parameter->getType();
        
        if ($type && !$type->isBuiltin()) {
            $className = $type->getName();
            return class_exists($className) ? new $className() : null;
        }

        $paramName = $parameter->getName();
        return $params[$paramName] ?? throw new \Exception("Invalid URL Parameter");
    }
}
