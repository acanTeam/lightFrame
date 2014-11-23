<?php
namespace DataStructure;

use PreDefine/Iterator;
use PreDefine/ArrayAccess;
use PreDefine/Countable;

class SplFixedArray implements Iterator, ArrayAccess, Countable
{
	/**
	 * @param $size int
	 * @return void
	 */
	public function __construct($size = 0)
	{}

	/**
	 * @param $array array
	 * @param $save_indexes boolean
	 * @return SplFixedArray
	 */
	public function fromArray($array, $save_indexes = true)
	{}

	/**
	 * @return int
	 */
	public function getSize()
	{}

	/**
	 * @param $size int
	 * @return int
	 */
	public function setSize($size)
	{}

	/**
	 * @return void
	 */
	public function toArray()
	{}
}
