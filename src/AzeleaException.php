<?php
namespace Azelea\Core;

/**
 * Custom Exception handler.
 * Calls a UI with data from stacktrace.
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
}
