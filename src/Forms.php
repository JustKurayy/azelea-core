<?php
namespace Azelea\Core;

class Forms {
    private $fieldHtml = array(); //stores all the fields, also used for loading them in
    private $formField; //stores the input field
    private $csrf;

    /**
     * Generates a CSRF token.
     * @param string $name
     */
    private function generateCsrf(string $name) {
        $_SESSION['_csrf'][$name] = bin2hex(random_bytes(32)); //bin2hex is safer than md5
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
                    $this->formField = "<input type='".$field['type']."' name='".$field['name']."' class='".$field['classes']."' id='".$field['name']."'";

                    if (array_key_exists("options", $field)) { //checks if the options field even exists
                        $this->parseFieldOptions($field["options"], $field);
                        $this->formField .= ">"; //closes the field
                        if (array_key_exists("wrapped", $field["options"])) { //wraps the field in a div
                            $this->formField = "<div class='".$field["options"]["wrapped"]."'>" . $this->formField . "</div>";
                        }
                    } else {
                        $l = "<label id='".$field['name']."'>".$field['name']."</label>";
                        array_push($this->fieldHtml, $l);
                        $this->formField .= "required='required'>";
                    }

                    array_push($this->fieldHtml, $this->formField); //always loads the field itself
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
     * Loops through the options field, if it exists, and adjusts the form to that.
     * Starts with the fallback, then adds existing options.
     * @param array $options
     * @param mixed $field
     */
    private function parseFieldOptions(array $options, array $form) {
        //fallback for if the key doesn't exist
        if (!array_key_exists("required", $options)) $this->formField .= "required='required'";
        if (!array_key_exists("label", $options)) array_push($this->fieldHtml, "<label id='".$form['name']."'>".$form['name']."</label>");

        //loops through the options and adds them if they exist
        foreach ($options as $o => $v) {
            switch ($o) {
                case "label":
                    ($v !== false) ? array_push($this->fieldHtml, "<label id='".$form['name']."'>".$form['name']."</label>") : "";
                    break;
                case "required":
                    ($v === false) ? $this->formField .= "" : $this->formField .= "required='required'";
                    break;   
            }
        }
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
     * Very basic right now. Do NOT rely on it!
     * @param mixed $data
     * @return mixed
     */
    private function filterField($data) {
        if (filter_var($data, FILTER_VALIDATE_INT)) return filter_var(trim($data), FILTER_SANITIZE_NUMBER_INT);
        if (is_string($data)) return htmlspecialchars(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Parses the form. Checks for CSRF token to ensure only 1 form is loaded at a time.
     * @return bool
     */
    public function submitForm(): bool {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['_csrf'] === $_SESSION['_csrf'][$this->csrf]) {
                $_SESSION['_csrf'] = [];
                //TODO: foreach filter check if valid
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
        return $this->filterField($_POST[$id]); //double check
    }
}
