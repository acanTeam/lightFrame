<?php
namespace PreDefine;

class Exception
{
	protected $message; // string
	protected $code; // int
	protected $file; // string
	protected $line; // int

	/**
	 * @param $message string 
	 * @param $code int
	 * @param $previous Exception
	 * @return void
	 */
	public function __construct($message = '', $code = 0, $previous = null)
	{}

	/**
	 * @return string
	 */
	final public function getMessage()
	{}

	/**
	 * @return Exception
	 */
	final public function getCode()
	{}

	/**
	 * @return string
	 */
	final public function getFile()
	{}

	/**
	 * @return int
	 */
	final public function getLine()
	{}

	/**
	 * @return array
	 */
	final public function getTrace()
	{}

	/**
	 * @return string
	 */
	final public function getTraceAsString()
	{}

	/**
	 * @return string
	 */
	public function __toString()
	{}

	/**
	 * @return void
	 */
	final private __clone()
	{}
}
