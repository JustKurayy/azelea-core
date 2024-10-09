<?php
namespace Azelea\Core;

class Controller {
    /**
     * Adds the html page to the screen
     * @param mixed $view
     * @param array $data
     * @return void
     */
    public function render($view, $data = []) {
        extract($data); //turns the array into multiple variables
        include "../src/pages/" . $view;
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
        $c = new $class;
        $c->init();
        return $c;
    }
}
