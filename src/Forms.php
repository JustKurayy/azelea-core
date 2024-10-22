<?php
namespace Azelea\Core;

class Forms {
    private $fieldHtml = []; //stores all the fields, also used for loading them in
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
                    $this->formField = "<input type='text' name='".$field['name']."' class='".$field['classes']."' id='".$field['name']."'";
                    $this->checkers($field);
                    array_push($this->fieldHtml, $this->formField); //always loads the field itself
                    break;
                case "label":
                    $l = "<label class='".$field['classes']."'>".$field['name']."</label>";
                    array_push($this->fieldHtml, $l);
                    break;
                case "password":
                    $this->formField = "<input type='password' name='".$field['name']."' class='".$field['classes']."' id='".$field['name']."'";
                    $this->checkers($field);
                    array_push($this->fieldHtml, $this->formField); //always loads the field itself
                    break;
                case "email":
                    $this->formField = "<input type='email' name='".$field['name']."' class='".$field['classes']."' id='".$field['name']."'";
                    $this->checkers($field);
                    array_push($this->fieldHtml, $this->formField); //always loads the field itself
                    break;
                case "submit":
                    if (empty($_SESSION['_csrf'][$field['name']])) $this->generateCsrf($field['name']);
                    $this->csrf = $field['name'];
                    $b = "<button type='submit' class='".$field['classes']."'>".$field['name']."</button>";
                    array_push($this->fieldHtml, $b);
                    break;
            }
        }
        array_push($this->fieldHtml, "<input type='hidden' name='_csrf' value='".$_SESSION['_csrf'][$this->csrf]."'>");
    }

    /**
     * Is repeated multiple times, used to check for form options.
     * @param array $field
     * @return void
     */
    private function checkers($field) {
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
        try {
            if (count($this->fieldHtml) !== 0) {
                return "<form class='$classes' method='post'>".implode($this->fieldHtml)."</form>";
            } else {
                throw new \Exception("There are no form fields to process (FORM CLASS NULL)");
            }
        } catch(\Exception $e) {
            Core::error($e);
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

                $arr = [];
                $i = 0;
                foreach($this->fieldHtml as $field) {
                    if (str_contains($field, "<input")) {
                        array_push($arr, $i); 
                        $i++;
                    }
                }

                if (count($_POST) != count($arr)) return $this->formFalse("Something went wrong");
                foreach ($_POST as $post) {
                    if (empty($post)) return $this->formFalse("Fill in correct data");
                }

                return true;
            } else {
                return $this->formFalse("Something went wrong");
            }
        }
        return false; //incase the button isn't pressed
    }

    private function formFalse(string $message) {
        Session::setSessionKey('flashes', [
            'message'=> $message,
            'type' => 'danger'
        ]);
        return false;
    }

    /**
     * Picks up the filtered data from the form
     * @param string $id
     */
    public function getData(string $id) {
        return $this->filterField($_POST[$id]); //double check
    }
}
