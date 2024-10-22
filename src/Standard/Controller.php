<?php
namespace Azelea\Core\Standard;
use Azelea\Core\Core;
use Azelea\Core\Session;
use Azelea\Templater\Loom;

class Controller {
    private array $flashMessages = [];

    /**
     * Adds the html page to the screen
     * @param string $view
     * @param array $data
     * @return void
     */
    public function render(string $view, $data = []) {
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
     * @param string $class
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

    /**
     * Adds a flash message and stores it in the session. 
     * Possible types: danger, warning, success.
     * @param string $message The text of the message
     * @param string $type The type of flash message, also used for the style (optional)
     * @return void
     */
    public function addFlash(string $message, string $type = null) {
        return Session::addFlash($message, $type);
    }
}
