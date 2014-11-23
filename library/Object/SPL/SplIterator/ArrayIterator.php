<?php
namespace SplIterator;

use PreDefine/Countable;
use PreDefine/ArrayAccess;
use PreDefine/Serializable;
use PreDefine/SeekableIterator;

class ArrayIterator implements Countable, ArrayAccess, Serializable, SeekableIterator
{
	/**
	 * @param $value array
	 * @return void
	 */
	public function __construct()
	{}

	/**
	 * @param $value mixed
	 * @return void
	 */
	public function append($value)
	{}

	/**
	 * @return void
	 */
	public function asort()
	{}

	/**
	 * @return void
	 */
	public function ksort()
	{}

	/**
	 * @return void
	 */
	public function natcasesort()
	{}

	/**
	 * @return void
	 */
	public function natsort()
	{}

	/**
	 * @param $cmp_function string
	 * @return void
	 */
	public function uasort($cmp_function)
	{}

	/**
	 * @param $cmp_function string
	 * @return void
	 */
	public function uksort($cmp_function)
	{}

	/**
	 * @return void
	 */
	public function getFlags()
	{}

	/**
	 * @param $flags string
	 * @return void
	 */
	public function setFlags($flags)
	{}

	/**
	 * @return array
	 */
	public function getArrayCopy()
	{}
}
