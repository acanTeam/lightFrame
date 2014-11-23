<?php
namespace SplIterator;

class CallbackFilterIterator extends FilterIterator
{
	/**
	 * @param $iterator Iterator
	 * @param $callback callable
	 * @return void
	 */
	public function __construct($iterator, $callback)
	{}
}

