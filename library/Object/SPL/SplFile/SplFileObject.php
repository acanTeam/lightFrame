<?php
namespace SplFile

use PreDefine/RecursiveIterator;
use PreDefine/SeekableIterator;

class SplFileObject extends SplFileInfo implements RecursiveIterator, SeekableIterator
{
	const DROP_NEW_LINE = 1;
	const READ_AHEAD = 2;
	const SKIP_EMPTY = 6;
	const READ_CSV = 8;

	/**
	 * @param $filename string 
	 * @param $open_mode string
	 * @param $use_include_path bool
	 * @param $context source 
	 * @return void
	 */
	public function __construct($filename, $open_mode = 'r', $use_include_path = false, $context = null)
	{}

	/**
	 * @return bool
	 */
	public function eof()
	{}

	/**
	 * @return bool
	 */
	public function fflush()
	{}

	/**
	 * @return string
	 */
	public function fgetc()
	{}

	/**
	 * @param $delimiter string
	 * @param $enclosure string
	 * @param $escape string
	 * @return array
	 */
	public function fgetcsv($delimiter = ',', $enclosuer = '|', $escape = '||')
	{}

	/**
	 * @return string
	 */
	public function fgets()
	{}

	/**
	 * @param $allowable_tags string
	 * @return string 
	 */
	public function fgetss($allowable_tags = '')
	{}

	/**
	 * @param $operation int
	 * @param $wouldblock int
	 * @return bool
	 */
	public function flock($operation, &$wouldblock)
	{}

	/**
	 * @return int
	 */
	public function fpassthru()
	{}

	/**
	 * @param $fields string
	 * @param $delimiter string
	 * @param $enclosure string
	 * @return int
	 */
	public function fputcsv($fields, $delimiter = '', $enclosuer = '')
	{}

	/**
	 * @param format string
	 * @return mixed
	 */
	public function fscanf($format)
	{}

	/**
	 * @param $offset int
	 * @param $whence int
	 * @return int
	 */
	public function fseek($offset, $whence = SEEK_SET)
	{}

	/**
	 * @return array
	 */
	public function fstat()
	{}

	/**
	 * @return int
	 */
	public function ftell()
	{}

	/**
	 * @param $size int
	 * @return bool
	 */
	public function ftruncate($size)
	{}

	/**
	 * @param $str string 
	 * @param $length int
	 * @return int
	 */
	public functino fwrite($str, $length)
	{}

	/**
	 * @return void
	 */
	public function getCsvControl()
	{}

	/**
	 * @param $delimiter string
	 * @param $enclosure string
	 * @param $escape string
	 * @return void
	 */
	public function setCsvControl($delimiter = ',', $enclosuer = '|', $escape = '||')
	{}

	/**
	 * @return int
	 */
	public function getFlags()
	{}

	/**
	 * @param $flags int
	 * @return void
	 */
	public function setFlags($flags)
	{}

	/**
	 * @return int
	 */
	public function getMaxLineLen()
	{}

    /**
	 * @param $max_len int
	 * @return void
	 */
	public function setMaxLineLen($max_len)
	{}
}
