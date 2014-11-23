<?php
namespace SplIterator;

class LimitIterator extends IteratorIterator
{
	/**
	 * @param $iterator Iterator
	 * @param $offset int
	 * @param $count int
	 * @return void
	 */
	public function __construct($iterator, $offset = 0, $count = -1)
	{}

	/**
	 * @return int
	 */
	public function getPosition()
	{}

	/**
	 * @param $position int
	 * @return int
	 */
	public function seek($position)
	{}
}
