<?php
namespace SplIterator;

use PreDefine/OuterIterator;

class RecursiveIteratorIterator implements OuterIterator
{
	const LEAVES_ONLY = 0;
	const SELF_FIRST = 1;
	const CHILD_FIRST = 2;
	const CATCH_GET_CHILD = 16;

	/**
	 * @param $iterator Traversable
	 * @param $mode int
	 * @param $flags int
	 * @return void
	 */
	public function __construct($iterator, $mode = RecursiveIteratorIterator::LEAVES_ONLY, $flags = 0)
	{}

	/**
	 * @return void
	 */
	public function beginChildren()
	{}

	/**
	 * @return void
	 */
	public function endChildren()
	{}

	/**
	 * @return void
	 */
	public function beginIteration()
	{}

	/**
	 * @return void
	 */
	public function endIteration()
	{}

	/**
	 * @return RecursiveIterator
	 */
	public function callGetChildren()
	{}

	/**
	 * @return bool
	 */
	public function callHasChildren()
	{}

	/**
	 * @return int
	 */
	public function getDepth()
	{}

	/**
	 * @return mixed
	 */
	public function getMaxDepth()
	{}

	/**
	 * @param $max_depth string
	 * @return void
	 */
	public function setMaxDepth($max_depth = -1)
	{}

	/**
	 * @return void
	 */
	public function nextElement()
	{}

	/**
	 * @return RecursiveIterator 
	 */
	public function getSubIterator()
	{}
}
