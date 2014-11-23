<?php
namespace SplIterator;

use PreDefine/RecursiveIterator;

class RecursiveFilterIterator extends FilterIterator implements RecursiveIterator
{
	/**
	 * @param $iterator RecursiveIterator
	 * @return void
	 */
	public function __construct($iterator)
	{}
}
