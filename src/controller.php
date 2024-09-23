<?php 
class Controller {
    public function render($view, $data = []) {
        extract($data);
        include "../src/pages/" . $view;
    }

    public function routeToUri(string $route) {
        header("Location: $route");
        return;
    }
}
