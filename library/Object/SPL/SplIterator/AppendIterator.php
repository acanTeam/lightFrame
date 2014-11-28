<?php
namespace SplIterator;

class AppendIterator extends IteratorIterator
{
	/**
	 * @return void
	 */
	public function __construct(void)
	{}

	/**
	 * @param $iterator Iterator
	 * @return void
	 */
	public function append($iterator)
	{}

	/**
	 * @return void
	 */
	public function getArrayIterator()
	{}

	/**
	 * @return Iterator
	 */
	public function getInnerIterator()
	{}

	/**
	 * @return int
	 */
	public function getIteratorIndex()
	{}
}
