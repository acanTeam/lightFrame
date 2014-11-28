<?php
namespace DataStructure;

use PreDefine/Iterator;
use PreDefine/Countable;

abstract class SplHeap implements Iterator, Countable
{
	public function __construct()
	{}

	/**
	 * @param $value1 mixed
	 * @param $value2 mixed
	 * @return int
	 */
	abstract public function compare($value1, $value2);

	/**
	 * @return mixed
	 */
	public function extract()
	{}

	/**
	 * @param $value mixed
	 * @return void
	 */
	public function insert($value)
	{}

	/**
	 * @return boolean
	 */
	public function isEmpty()
	{}

	/**
	 * @return void
	 */
	public function recoverFromCorruption()
	{}

	/**
	 * @return mixed
	 */
	public function top()
	{}
}
