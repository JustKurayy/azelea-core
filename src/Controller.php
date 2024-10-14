<?php
namespace Azelea\Core;
use Azelea\Templater\Loom;

class Controller {
    /**
     * Adds the html page to the screen
     * @param mixed $view
     * @param array $data
     * @return void
     */
    public function render($view, $data = []) {
        try {
            //extract($data); //turns the array into multiple variables
            if (!str_contains($view, ".loom.")) throw new \Exception("Page is not a .loom.php templater file");
            $loom = new Loom($data);
            return $loom->render($view);
        } catch (\Exception $e) {
            return Core::error($e);
        }
    }

    public function json($data) {
        header("content-type: application/json");
        echo $data;
    }

    /**
     * Routes the user to another page
     * @param string $route
     */
    public function routeToUri(string $route) {
        header("Location: $route");
        return;
    }

    /**
     * Creates an form. 
     * @param string $item
     * @param mixed $item
     * @return class
     */
    public function buildForm(string $class, $item) {
        try {
            $c = new $class;
            $c->init();
            return $c;
        } catch (\Exception $e) {
            return Core::error($e);
        }
    }
}
