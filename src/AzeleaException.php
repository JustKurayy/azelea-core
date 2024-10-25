<?php
namespace Azelea\Core;

/**
 * Custom Exception handler.
 * Contains fault manager.
 */
class AzeleaException extends \Exception {
    /**
     * Construction script.
     * @param string $message (optional)
     * @param int $code (optional)
     */
    public function __construct(string $message = '', int $code = 422) {
        parent::__construct($message, $code);
        $this->message = "$message";
        $this->code = $code;
        Core::error($this); //Calls the static function containing the UI
    }

    /**
     * Debugs given param.
     * @param mixed $item
     * @return exit
     */
    static function dd($item) {
        echo "<script>document.body.innerHTML = '';</script>"; // Empties the entire screen
        echo "<style>
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
                content: '▶';
                display: inline-block;
                width: 1em;
                transition: transform 0.2s;
            }
            .dd-toggle.open::before {
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
        </style>";
    
        echo "<div class=\"dd-container\">";
        echo "<div class=\"dd-item dd-toggle\" onclick=\"this.nextElementSibling.classList.toggle('dd-content'); this.classList.toggle('open');\">";
        echo (is_object($item)) ? "<button><strong>".$item::class."</strong></button>" : ((is_array($item)) ? "<button><strong>Array</strong></button>" : "<button><strong>".htmlspecialchars($item)."</strong></button>");
        echo "</div>";
        echo "<div class=\"dd-content\">";
        
        echo self::renderItem($item, 1);
        
        echo "</div>";
        echo "</div>";
        
        exit;
    }

    /**
     * Shows stacktrace error
     * @param \Throwable $exception
     * @return null
     */
    static function error(\Throwable $exception) {
        // Clear the document body
        echo "<script>document.body.innerHTML = '';</script>";
        $messg = "";
        switch ($exception->getCode()) {
            case 1045:
                $messg = "Database Credentials do not match. Possibly wrong username or password.";
                break;
        }
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
                background-color: #1e1e1e;
                padding: 10px;
                border-radius: 5px;
                overflow-x: auto;
                margin: 5px 0;
                color: #dcdcdc;
            }
            .highlight {
                background-color: #756628;
                display: inline-block;
                padding: 0 5px;
            }
            .line-number {
                color: #aaa;
                display: inline-block;
                width: 10px;
                text-align: right;
                margin-right: 20px;
            }
            .line {
                display: flex;
                align-items: center;
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
                    echo "<div class='code'>";
                    echo "<strong>File:</strong> " . htmlspecialchars($file) . " <strong>Line:</strong> " . htmlspecialchars($line) . "<br>";

                    // Get the lines of code
                    $lines = file($file);
                    $start = max(0, $line - 3); // 3 lines before
                    $end = min(count($lines) - 1, $line + 1); // 1 line after

                    for ($i = $start; $i <= $end; $i++) {
                        echo "<span class='line-number'>" . ($i + 1) . "</span>";
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
                            echo "<div class='code'>";
                            echo "<strong>File:</strong> " . htmlspecialchars($file) . " <strong>Line:</strong> " . htmlspecialchars($line) . "<br>";

                            $lines = file($file);
                            $start = max(0, $line - 3); // 3 lines before
                            $end = min(count($lines) - 1, $line + 1); // 1 line after

                            for ($i = $start; $i <= $end; $i++) {
                                echo "<div class='line'>";
                                echo "<span class='line-number'>" . ($i + 1) . "</span>";
                                if ($i === $line - 1) {
                                    echo "<span class='highlight'>" . htmlspecialchars(trim($lines[$i])) . "</span>";
                                } else {
                                    echo "<span>" . htmlspecialchars(trim($lines[$i])) . "</span>";
                                }
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        die();
    }

    /**
     * Renders items for dd().
     * Supports nested classes and arrays.
     * @param mixed $item
     * @param int $depth The amount of tab space the first nest should have
     * @return string
     */
    private static function renderItem($item, int $depth = 1) {
        if (is_array($item)) {
            $output = "<div class=\"dd-array\">";
            foreach ($item as $key => $value) {
                $output .= "<div class=\"dd-item dd-toggle\" onclick=\"this.nextElementSibling.classList.toggle('dd-content'); this.classList.toggle('open');\" style=\"margin-left: " . ($depth * 20) . "px;\">";
                $output .= "<button><strong>" . gettype($value) . " => " . htmlspecialchars("[$key]") . "</strong></button>";
                $output .= "</div>";
                $output .= "<div class=\"dd-content\">" . self::renderItem($value, $depth + 1) . "</div>";
            }
            $output .= "</div>";
            return $output;
        } elseif (is_object($item)) {
            $reflection = new \ReflectionObject($item);
            $properties = $reflection->getProperties();
            $output = "<div class=\"dd-object\">";
            
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($item);
                $output .= "<div class=\"dd-item dd-toggle\" onclick=\"this.nextElementSibling.classList.toggle('dd-content'); this.classList.toggle('open');\" style=\"margin-left: " . ($depth * 20) . "px;\">";
                $output .= "<button><strong>" . $property->getType() . " => " . htmlspecialchars($property->getName()) . "</strong></button>";
                $output .= "</div>";
                $output .= "<div class=\"dd-content\">" . self::renderItem($value, $depth + 1) . "</div>";
            }
            
            $output .= "</div>";
            return $output;
        } else {
            return "<pre class=\"dd-item\" style=\"margin-left: " . ($depth * 20) . "px;\">" . gettype($item) . " => " . htmlspecialchars(var_export($item, true)) . "</pre>";
        }
    }
}
