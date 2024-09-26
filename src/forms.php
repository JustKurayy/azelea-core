<?php
namespace Azelea;

class Forms {
    private $fieldHtml = array();
    private $csrf;

    /**
     * Generates a CSRF token.
     * @param string $name
     */
    private function generateCsrf(string $name) {
        $_SESSION['_csrf'][$name] = bin2hex(random_bytes(32));
        $this->csrf = $_SESSION['_csrf'][$name];
        return;
    }

    /**
     * Generates an array of html input fields to display the form
     * @param array $fields
     */
    public function generateFields(array $fields) {
        foreach ($fields as $field) {
            switch ($field["type"]) {
                case "text":
                    $f = "<input type='".$field['type']."' name='".$field['name']."' class='".$field['classes']."' id='".$field['name']."'";

                    if (array_key_exists("options", $field)) { //only removes the label if it is specifically defined not to load
                        try {
                            if ($field["options"]["label"] == false) {
                                array_push($this->fieldHtml, $f);
                            } else {
                                $l = "<label id='".$field['name']."'>".$field['name']."</label>";
                                array_push($this->fieldHtml, $l);
                            }
                            if ($field["options"]["required"] == false) {
                                $f .= "required";
                            }
                        } catch (\Exception $e) {
                            //leaving it empty as no error log is needed here. 
                        }
                    } else {
                        $l = "<label id='".$field['name']."'>".$field['name']."</label>";
                        array_push($this->fieldHtml, $l);
                    }

                    $f .= ">";
                    array_push($this->fieldHtml, $f); //always loads the field itself
                    break;
                case "label":
                    $l = "<label class='".$field['classes']."'>".$field['name']."</label>";
                    array_push($this->fieldHtml, $l);
                    break;
                case "submit":
                    if (empty($_SESSION['_csrf'][$field['name']])) {
                        $this->generateCsrf($field['name']);
                    }
                    $this->csrf = $field['name'];

                    $b = "<input type='submit' class='".$field['classes']."' name='submit' value='".$field['name']."'>";
                    array_push($this->fieldHtml, $b);
                    break;
            }
        }
        array_push($this->fieldHtml, "<input type='hidden' name='_csrf' value='".$_SESSION['_csrf'][$this->csrf]."'>");
    }

    /**
     * Shows the form on the page.
     * Argument is passed as html class(es).
     * Argument is optional.
     * @param string $classes
     */
    public function show(string $classes = null) {
        if (count($this->fieldHtml) !== 0) {
            echo "<form class='$classes' method='post'>".implode($this->fieldHtml)."</form>";
        } else {
            //should insert faultmanager here
        }
    }

    /**
     * Filters the input data, to prevent sql injection or any other trouble.
     * Remove ? from ?array, as it should return the data in array for further parsing
     * @param $data
     * @return array
     */
    private function filterFields($data): ?array {
        return null;
    }

    /**
     * Parses the form. Checks for CSRF token to ensure only 1 form is loaded at a time.
     * @return bool
     */
    public function submitForm(): bool {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['_csrf'] === $_SESSION['_csrf'][$this->csrf]) {
                $_SESSION['_csrf'] = [];
                return true;
            } else {
                return false;
            }
        }
        return false; //incase the button isn't pressed
    }

    /**
     * Picks up the filtered data from the form
     * @param string $id
     */
    public function getData(string $id) {
        return $_POST[$id]; //temp solution
    }
}
