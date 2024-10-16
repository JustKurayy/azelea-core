<?php
namespace Azelea\Core\Standard;
use Azelea\Core\Core;
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
    public function buildForm(string $class, $item = null) {
        try {
            $c = new $class;
            $c->init();
            return $c;
        } catch (\Exception $e) {
            return Core::error($e);
        }
    }
}
