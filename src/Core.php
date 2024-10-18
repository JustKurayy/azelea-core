<?php
namespace Azelea\Core;
use Azelea\Core\Session;

/**
 * The kernel of the Azelea Framework. 
 * Used for initializing the page. 
 * Contains error managers too.
 */
class Core {
    private array $readablePaths = ['/src/controllers', '/src/forms', '/src/models'];
    private $path;
    private $sessionManager;
    private $routes;

    public function __construct($path) {
        $this->path = $path;
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname($this->path), ".env.local");
            $dotenv->load();
        } catch (\Exception $e) {
            return Core::error($e);
        }

        foreach ($this->readablePaths as $p) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath(dirname($this->path).$p))) as $filename) {
                if (str_contains($filename, ".php")) {
                    include $filename;
                }
            }
        }

        set_exception_handler([$this, 'error']); //sets a custom exception handler
        
        $this->sessionManager = new Session();
        if(isset($_SESSION['flashes'])) {
            $_SESSION['flashes'] = null;
            unset($_SESSION['flashes']);
        }
        
        try {
            if (!file_exists($this->path . "/../src/routes.php")) throw new \Exception("Routes.php not found, cannot forward to page.");
            include $this->path . "/../src/routes.php";
            $this->routes = new Routes();
        } catch (\Exception $e) {
            Core::error($e);
        }
    }

    /**
     * Debugs given param.
     * @param mixed $item
     * @return exit
     */
    public static function dd($item) {
        AzeleaException::dd($item);
    } 
    
    /**
     * Shows stacktrace error
     * @param \Throwable $exception
     * @return null
     */
    public static function error(\Throwable $exception) {
        AzeleaException::error($exception);
    }
}
