<?php

/**
 * Message class to return message information.
 */
class Message
{
	public $message;
	public $code;

	public static $OK = 0;
	public static $ERROR = -1;
	public static $PASSWORD_REQUIRED = 1;
	public static $INVALID_PASSWORD = 2;

	/**
	 * Create a new message object with code and message.
	 * 
	 * @param integer $code Error code to set.
	 * @param string $message Error message to set.
	 */
	function __construct($code = NULL, $message = NULL) {
	  $this->code = $code;
	  $this->message = $message;
	}
}
