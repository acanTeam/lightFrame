<?php
namespace SplIterator;

use PreDefine/Countable;
use PreDefine/ArrayAccess;

class CachingIterator extends IteratorIterator implements Countable, ArrayAccess
{
	const CALL_TOSTRING = 1;
	const CATCH_GET_CHILD = 16;
	const TOSTRING_USE_KEY = 2;
	const TOSTRING_USE_CURRENT = 4;
	const TOSTRING_USE_INNER = 8;
	const FULL_CACHE = 256;

	/**
	 * @param $iterator Iterator
	 * @param $flags string
	 */
	public function __construct($iterator, $flags = self::CALL_TOSTRING)
	{}

	/**
	 * @return void
	 */
	public function getCache()
	{}

	/**
	 * @return void
	 */
	public function hasNext()
	{}

	/**
	 * @param $falgs bitmask
	 * @return void
	 */
	public function setFlags($flags)
	{}

	/**
	 * @return void
	 */
	public function __toString()
	{}
}
