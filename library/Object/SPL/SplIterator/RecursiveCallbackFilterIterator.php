<?php
namespace SplIterator;

use PreDefine/RecursiveIterator;

class RecursiveCallbackFilterItarator extends CallbackFilterIterator implements RecursiveIterator
{
	/**
	 * @param $iterator RecursiveIterator
	 * @param $callback string
	 * @return void
	 */
	public function __construct($iterator, $callback)
	{}
}
