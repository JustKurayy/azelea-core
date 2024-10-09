<?php
namespace Azelea\Core;
use Azelea\Core\Session;

class Core {
    private $path;
    private $sessionManager;
    private $routes;
    private array $stacktrace = [];

    public function __construct($path) {
        $this->path = $path;
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($this->path), ".env.local");
        $dotenv->load();
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath(dirname($this->path).'/src/controllers'))) as $filename) {
            if (str_contains($filename, ".php")) {
                include $filename;
            }
        }
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath(dirname($this->path).'/src/forms'))) as $filename) {
            if (str_contains($filename, ".php")) {
                include $filename;
            }
        }
        
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath(dirname($this->path).'/src/models'))) as $filename) {
            if (str_contains($filename, ".php")) {
                include $filename;
            }
        }

        $this->sessionManager = new Session();

        include $this->path . "/../src/routes.php";
        $this->routes = new Routes();
    }

    /**
     * Debugs given param.
     * @param mixed $item
     * @return exit
     */
    static function dd($item) {
        echo "<script>document.body.innerHTML = '';</script>"; //empties the entire screen
        echo '<style>
            .dd-container {
                font-family: Arial, sans-serif;
                background-color: #222;
                color: #fff;
                padding: 10px;
                border-radius: 5px;
            }
            .dd-item {
                margin: 5px 0;
                cursor: pointer;
                padding: 5px;
                border: 1px solid #444;
                border-radius: 3px;
            }
            .dd-content {
                display: none;
                margin-left: 20px;
                padding: 10px;
                background-color: #333;
                border-radius: 3px;
            }
            .dd-toggle::before {
                content: "▶";
                display: inline-block;
                width: 1em;
                transition: transform 0.2s;
            }
            .dd-toggle.open::before {
                /*content: "▼";*/
                transform: rotate(90deg);
            }
            button {
                background: none;
	            color: inherit;
	            border: none;
	            padding: 0;
	            font: inherit;
	            cursor: pointer;
	            outline: inherit;
            }
        </style>';
        
        echo '<div class="dd-container">';
        echo '<div class="dd-item dd-toggle" onclick="this.nextElementSibling.classList.toggle(\'dd-content\'); this.classList.toggle(\'open\');">';
        echo (is_object($item)) ? '<button><strong>'.$item::class.'</strong></button>' : '<button><strong>'.$item.'</strong></button>';
        echo '</div>';
        echo '<div class="dd-content">';
        
        if (is_object($item)) {
            $reflection = new \ReflectionObject($item);
            $properties = $reflection->getProperties();
            $result = [];

            foreach ($properties as $property) {
                $property->setAccessible(true);
                $result[$property->getName()] = $property->getValue($item);
            }

            echo '<pre class="dd-item">' . htmlspecialchars(print_r($result, true)) . '</pre>';
        } else {
            echo '<pre class="dd-item">' . htmlspecialchars(var_export($item, true)) . '</pre>';
        }

        echo '</div>';
        echo '</div>';
        
        exit;
    }

    /**
     * Shows stacktrace error. WIP!
     * @param mixed $item
     * @return exit
     */
    static function error($item) {
        echo "<script>document.body.innerHTML = '';</script>";
        $excp = new \Exception();
        ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Azelea Error</title>
            </head>
            <style>
                body{height:100vh;background-color:#3d3938;font-family:Arial, Helvetica, sans-serif;}
                .wrapper{display:flex;justify-content:center;}
                .centerdiv{width:1300px;background-color:#872330;padding:20px;color:white;}
            </style>
            <body>
                <div class="wrapper">
                    <div class="centerdiv"><h2><?=$item?></h2></div>
                    <?php
                        $excp->getLine();
                    ?>
                </div>
            </body>
            </html>
        <?php
        die();
    }
}
