<?php

namespace Azelea\Core\Standard;

use Azelea\Core\Core;
use Azelea\Core\Session;
use Azelea\Templater\Loom;

class Controller
{
    private array $flashMessages = [];

    /**
     * Adds the html page to the screen
     * @param string $view
     * @param array $data
     * @return void
     */
    public function render(string $view, $data = [])
    {
        if (str_contains($view, ".json")) {
            $this->cmsConvert($view);
        } else {
            try {
                $loom = new Loom($data);
                return $loom->render($view);
            } catch (\Exception $e) {
                return Core::error($e);
            }
        }
    }

    public function json($data)
    {
        header("content-type: application/json");
        echo $data;
    }

    /**
     * Routes the user to another page
     * @param string $route
     */
    public function routeToUri(string $route)
    {
        header("Location: $route");
        return;
    }

    /**
     * Creates an form. 
     * @param string $class
     * @param mixed $item
     * @return class
     */
    public function buildForm(string $class, $item = null)
    {
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
    public function addFlash(string $message, string $type = null)
    {
        return Session::addFlash($message, $type);
    }

    //test json to html converter

    private function loadJson(string $view)
    {
        $dir = substr(dirname(__DIR__), 0, strpos(dirname(__DIR__), "\\vendor\\"));
        $file = file_get_contents($dir . "/src/pages/" . $view);
        return json_decode($file, true);
    }

    private function renderHead($json)
    {
        $head = '';

        // Render <meta> tags
        if (isset($json['meta']) && is_array($json['meta'])) {
            foreach ($json['meta'] as $meta) {
                $metaAttributes = '';
                foreach ($meta as $key => $value) {
                    $metaAttributes .= " {$key}=\"{$value}\"";
                }
                $head .= "<meta{$metaAttributes}>\n";
            }
        }

        // Render <title>
        if (isset($json['title'])) {
            $head .= "<title>{$json['title']}</title>\n";
        }

        // Render <script> tags
        if (isset($json['script']) && is_array($json['script'])) {
            foreach ($json['script'] as $script) {
                $src = isset($script['src']) ? " src=\"{$script['src']}\"" : '';
                $head .= "<script{$src}></script>\n";
            }
        }

        return $head;
    }

    private function renderHtml($json)
    {
        $html = '';

        $this->addHtml($json, $html);

        return $html;
    }

    private function addHtml(array $json, string $html)
    {
        $html = "";

        foreach ($json as $key => $value) {
            foreach ($value as $k => $item) {
                if ($this->isHtmlTag($item)) {
                    $html .= "$k ";
                    $html .= $this->addHtml($value[$item], $html);
                }
            }

            // echo $key;
            // $html .= "<$key>". is_string($value) ? $value : "" ."</$key>\n";
            $html .= "$key";
            Core::dd($html);
        }
        return $html;
    }

    /**
     * 
     */
    private function isHtmlTag($key)
    {
        $htmlTags = ['body', 'div', 'a', 'article', 'section', 'main', 'header', 'footer', 'span', 'nav'];
        return in_array($key, $htmlTags);
    }

    private function cmsConvert(string $view)
    {
        $json = $this->loadJson($view);
        $headContent = isset($json['head']) ? $this->renderHead($json['head']) : '';
        $bodyContent = $this->renderHtml($json['body']);

        // Final HTML stuff
        echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
        echo $headContent;
        echo "</head>\n<body>\n";
        echo $bodyContent;
        echo "</body>\n</html>";
    }
}
