<?php

class Forms {
    private $fieldHtml = array();

    /**
     * Generates an array of html input fields to display the form
     * @param array $fields
     */
    public function generateFields(array $fields) {
        
        foreach ($fields as $field) {
            switch ($field["type"]) {
                case "text":
                    $f = "<input type='".$field['type']."' name='".$field['name']."' class='".$field['classes']."'>";
                    array_push($this->fieldHtml, $f);
                    break;
                case "label":
                    $f = "<label class='".$field['classes']."'>".$field['name']."</label>";
                    array_push($this->fieldHtml, $f);
                    break;
            }
        }
    }

    public function show() {
        if (count($this->fieldHtml) !== 0) {
            echo "<form>".implode($this->fieldHtml)."</form>";
        } else {
            //should insert faultmanager here
        }
    }

    /**
     * Filters the input data, to prevent sql injection or any other trouble
     * @param $data
     */
    private function filterField($data) {

    }
}
