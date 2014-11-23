<?php
namespace DataStructure;

use PreDefine/Countable;
use PreDefine/ArrayAccess;
use PreDefine/Iterator;

class SplDoublyLinkedList implements Iterator, ArrayAccess, Countable
{
	public function __construct()
	{}

	/**
	 * @return mixed
	 */
	public function bottom()
	{}

	/**
	 * @return int
	 */
	public function getIteratorMode()
	{}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{}

	/**
	 * @return mixed
	 */
	public function pop()
	{}

	/**
	 * @return void
	 */
	public function prev()
	{}

	/**
	 * @param $value
	 * @return void
	 */
	public function push($value)
	{}

	/**
	 * @return string
	 */
	public function serialize()
	{}

	/**
	 * @param $mode int
	 * @return void
	 */
	public function setIteratorMode($mode)
	{}

	/**
	 * @return mixed
	 */
	public function shift()
	{}

	/**
	 * @return mixed
	 */
	public function top()
	{}

	/**
	 * @param $serialized string
	 * @return void
	 */
	public function unserialize($serialized)
	{}

	/**
	 * @param $value mixed
	 * @return void
	 */
	public function unshift($value)
	{}
}

