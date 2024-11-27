<?php

namespace Azelea\Core\Standard;

use Azelea\Core\Core;

class Converter
{
    /**
     * Converts JSON to HTML
     */
    public function __construct(string $view = null)
    {
        if (!empty($view)) {
            $json = $this->loadJson($view);
            $headContent = isset($json['head']) ? $this->renderHead($json['head']) : '';
            $bodyContent = $this->addHtml($json['body']);

            // Final HTML stuff
            echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n";
            echo $headContent;
            echo "</head>\n<body>\n";
            echo $bodyContent;
            echo "</body>\n</html>";
        }
    }

    /**
     * Used to collect the parsed JSON
     * to HTML.
     */
    private string $html = '';

    private function loadJson(string $view)
    {
        $dir = substr(dirname(__DIR__), 0, strpos(dirname(__DIR__), "\\vendor\\"));
        $file = file_get_contents($dir . "/src/pages/" . $view);
        return json_decode($file, true);
    }

    private function renderHead($json)
    {
        $head = '';

        if (isset($json['meta']) && is_array($json['meta'])) {
            foreach ($json['meta'] as $meta) {
                $metaAttributes = '';
                foreach ($meta as $key => $value) {
                    $metaAttributes .= " {$key}=\"{$value}\"";
                }
                $head .= "<meta{$metaAttributes}>\n";
            }
        }

        if (isset($json['title'])) {
            $head .= "<title>{$json['title']}</title>\n";
        }

        if (isset($json['script']) && is_array($json['script'])) {
            foreach ($json['script'] as $script) {
                $src = isset($script['src']) ? " src=\"{$script['src']}\"" : '';
                $head .= "<script{$src}></script>\n";
            }
        }

        return $head;
    }

    private function addHtml(array $json)
    {
        foreach ($json as $key => $value) {
            if (is_array($value)) {
                if ($this->isHtmlTag($key)) {
                    $classList = $this->getClassList($value);
                    $src = isset($value['src']) ? "src='" . $value['src'] . "' " : '';
                    $this->html .= "<$key" . ($classList ? " class='$classList'" : '') . ($src ? $src : '') . ">";

                    if (isset($value['children']) && is_array($value['children'])) {
                        // Core::dd($value['children']);
                        foreach ($value['children'] as $child) {
                            if (isset($child['block'])) {
                                // Core::dd($child['block']);
                                $block = $this->addBlock($child['block']['name']);
                                $this->html .= $block;
                            }
                            $this->addHtml($child);
                        }
                    }

                    if (isset($value['content'])) {
                        $this->html .= $value['content'];
                    }

                    $this->html .= "</$key>\n";
                }
            } else {
                if ($this->isHtmlTag($key)) {
                    $this->html .= "<$key>";
                    $this->html .= $value;
                    $this->html .= "</$key>\n";
                }
            }
        }

        return $this->html;
    }

    /**
     * Converts the classes from Array to String
     * @param array $item
     * @return string
     */
    private function getClassList(array $item)
    {
        $classList = '';
        if (isset($item['class'])) {
            $classes = is_array($item['class']) ? $item['class'] : explode(' ', $item['class']);

            foreach ($classes as $class) {
                $classList .= $class . ' ';
            }
        }

        return trim($classList);
    }

    /**
     * Check if the key corresponds to a valid HTML tag
     * @param string $key
     * @return bool
     */
    private function isHtmlTag(string $key)
    {
        $htmlTags = [
            'body',
            'div',
            'a',
            'article',
            'section',
            'main',
            'header',
            'footer',
            'span',
            'nav',
            'img',
            'ul',
            'li'
        ];
        for ($i = 1; $i <= 6; $i++) {
            array_push($htmlTags, 'h' . $i);
        }
        // Core::dd($htmlTags);
        return in_array($key, $htmlTags);
    }

    /**
     * Adds a pre-existing snippet of HTML. 
     * Currently the snippets are not editable.
     * With `$renderImmediately` on true, the block
     * immediately renders without awaiting the rest 
     * of the converter.
     * 
     * @param string $name Name of the block with .json at the end
     * @param bool $renderImmediately Renders the block immediately.
     * @return void
     */
    public function addBlock(string $name, bool $renderImmediately = false)
    {
        $dir = substr(dirname(__DIR__), 0, strpos(dirname(__DIR__), "\\vendor\\"));
        $file = file_get_contents($dir . "/src/blocks/" . $name);
        if ($renderImmediately) return $this->addHtml(json_decode($file, true));
        $this->addHtml(json_decode($file, true));
    }
}
