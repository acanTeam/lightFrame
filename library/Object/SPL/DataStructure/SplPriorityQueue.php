<?php
namespace DataStructure;

use PreDefine/Iterator;
use PreDefine/Countable;

class SplPriorityQueue implements Iterator, Countable
{
	public function __construct()
	{}

	/**
	 * @param $prirority1 mixed
	 * @param $prirority2 mixed
	 * @return int
	 */
	public function compare($priority1, $priority2)
	{}

	/**
	 * @return mixed
	 */
	public function extract()
	{}

	/**
	 * @param $value mixed
	 * @parma $priority
	 * @return void
	 */
	public function insert($value, $priority)
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
	 * @param $flags int
	 * @return void
	 */
	public function setExtractFlags($flags)
	{}

	/**
	 * @return mixed
	 */
	public function top()
	{}
}
