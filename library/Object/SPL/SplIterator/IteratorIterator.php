<?php
namespace SplIterator;

use PreDefine/OuterIterator;

class IteratorIterator implements OuterIterator
{
	/**
	 * @param $iterator Traversable
	 * @return void
	 */
	public function __construct($iterator)
	{}
}
