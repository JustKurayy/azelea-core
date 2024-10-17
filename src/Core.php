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

        $this->sessionManager = new Session();
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
        echo (is_object($item)) ? '<button><strong>'.$item::class.'</strong></button>' : ((is_array($item)) ? '<button><strong>Array</strong></button>' : '<button><strong>'.$item.'</strong></button>');
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
     * @param Exception $exception
     * @return exit
     */
    static function error(\Exception $exception) {
        // Clear the document body
        echo "<script>document.body.innerHTML = '';</script>";
        $messg = "";
        switch ($exception->getCode()) {
            case 1045:
                $messg = "Database Credentials do not match. Possibly wrong username or password.";
                break;
        }

        // Start outputting the HTML
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Azelea Error</title>
        </head>
        <style>
            body {
                height: 100vh;
                background-color: #3d3938;
                font-family: 'Courier New', Courier, monospace;
                color: white;
                margin: 0;
            }
            .wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                height: 100%;
            }
            .centerdiv {
                width: 1300px;
                background-color: #872330;
                padding: 20px;
                color: white;
                border-radius: 5px;
            }
            pre {
                background-color: #4a4343;
                padding: 10px;
                border-radius: 5px;
                overflow-x: auto;
                white-space: pre-wrap;
            }
            .code {
                background-color: #1e1e1e; /* VSCode background color */
                padding: 10px;
                border-radius: 5px;
                overflow-x: auto;
                margin: 5px 0;
                color: #dcdcdc; /* VSCode text color */
            }
            .highlight {
                background-color: #756628; /* Highlight color */
                display: inline-block;
                padding: 0 5px;
            }
            .line-number {
                color: #aaa; /* Muted line number color */
                display: inline-block;
                width: 10px; /* Fixed width for alignment */
                text-align: right; /* Align line numbers to the right */
                margin-right: 20px; /* Space between line number and code */
            }
            .line {
                display: flex; /* Flex to align line number and code */
                align-items: center; /* Center align */
            }
        </style>
        <body>
            <div class="wrapper">
                <div class="centerdiv">
                <?php
                    $line = $exception->getLine();
                    $file = $exception->getFile();

                    echo "<h2>".$exception->getMessage()."</h2>";
                    echo "<h4>".$messg."</h4>";
                    // Display the source code for the error line
                    echo "<div class='code'>";
                    echo "<strong>File:</strong> " . htmlspecialchars($file) . " <strong>Line:</strong> " . htmlspecialchars($line) . "<br>";

                    // Get the lines of code
                    $lines = file($file);
                    $start = max(0, $line - 3); // 3 lines before
                    $end = min(count($lines) - 1, $line + 1); // 1 line after

                    for ($i = $start; $i <= $end; $i++) {
                        // Display line number
                        echo "<span class='line-number'>" . ($i + 1) . "</span>";
                        // Highlight the line where the error occurred
                        if ($i === $line - 1) {
                            echo "<span class='highlight'>" . htmlspecialchars(trim($lines[$i])) . "</span><br>";
                        } else {
                            echo "<span>" . htmlspecialchars(trim($lines[$i])) . "</span><br>";
                        }
                    }
                    echo "</div>";?>
                    
                    <h3>Stack Trace:</h3>
                    
                    <?php
                    $trace = $exception->getTrace();
                    foreach ($trace as $entry) {
                        if (isset($entry['file']) && isset($entry['line'])) {
                            $file = $entry['file'];
                            $line = $entry['line'];
                            // Display the stack trace entry
                            echo "<div class='code'>";
                            echo "<strong>File:</strong> " . htmlspecialchars($file) . " <strong>Line:</strong> " . htmlspecialchars($line) . "<br>";
                            
                            // Get the surrounding lines of code
                            $lines = file($file);
                            $start = max(0, $line - 3); // 3 lines before
                            $end = min(count($lines) - 1, $line + 1); // 1 line after
                            for ($i = $start; $i <= $end; $i++) {
                                // Display line number and code in a flex container
                                echo "<div class='line'>";
                                echo "<span class='line-number'>" . ($i + 1) . "</span>";
                                // Highlight the line where the error occurred
                                if ($i === $line - 1) {
                                    echo "<span class='highlight'>" . htmlspecialchars(trim($lines[$i])) . "</span>";
                                } else {
                                    echo "<span>" . htmlspecialchars(trim($lines[$i])) . "</span>";
                                }
                                echo "</div>"; // Close the line container
                            }
                            echo "</div>"; // Close the code block
                        }
                    }
                    ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        // Stop the script
        die();
    }    
}
