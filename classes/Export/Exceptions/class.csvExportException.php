<?php

/**
 * Class csvExportException
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * @version 2.0.6
 */
class csvExportException extends Exception {

    const UNKNONWN_EXCEPTION = - 1;
    const COLUMN_DOES_NOT_EXIST = 1001;
    const COLUMN_DOES_ALREADY_EXISTS_IN_ROW = 1002;
    const INVALID_ARRAY = 2001;
    /**
     * @var array
     */
    protected static $message_strings = array(
        self::UNKNONWN_EXCEPTION => 'Unknown Exception',
        self::COLUMN_DOES_NOT_EXIST => 'Column does not exist:',
        self::COLUMN_DOES_ALREADY_EXISTS_IN_ROW => 'Column does already exist in row:',
        self::INVALID_ARRAY => 'Invalid array: ',
    );
    /**
     * @var string
     */
    protected $message = '';
    /**
     * @var int
     */
    protected $code = self::UNKNONWN_EXCEPTION;

    /**
     * @var string
     */
    protected $additional_info = '';


    /**
     * @param int    $exception_code
     * @param string $additional_info
     */
    public function __construct($exception_code = self::UNKNONWN_EXCEPTION, $additional_info = '') {
        $this->code = $exception_code;
        $this->additional_info = $additional_info;
        $this->assignMessageToCode();
        parent::__construct($this->message, $this->code);
    }


    protected function assignMessageToCode() {
        $this->message = 'ActiveRecord Exeption: ' . self::$message_strings[$this->code] . $this->additional_info;
    }


    /**
     * @return string
     */
    public function __toString() {
        return implode('<br>', array( get_class($this), $this->message ));
    }
}

?>