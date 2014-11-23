<?php
namespace SplIterator;

class RecursiveTreeIterator extends RecursiveIteratorIterator
{
	const BYPASS_CURRENT = 4;
	const BYPASS_KEY = 8;
	const PREFIX_LEFT = 0;
	const PREFIX_MID_HAS_NEXT = 1;
	const PREFIX_MID_LAST = 2;
	const PREFIX_END_HAS_NEXT = 3;
	const PREFIX_END_LAST = 4;
	const PREFIX_RIGHT = 5;

	/**
	 * @param $it RecursiveIterator|IteratorAggregate
	 * @param $flags int
	 * @param $cit_flags int
	 * @param $mode int
	 * @return void
	 */
	public function __construct($it, $flags = RecursiveTreeIterator::BYPASS_KEY, $cit_flags = CachingIterator::CATCH_GET_CHILD, $mode = RecursiveIteratorIterator::SELF_FIRST)
	{}

	/**
	 * @return string
	 */
	public function getEntry()
	{}

	/**
	 * @return void
	 */
	public function getPostfix()
	{}

	/**
	 * @return void
	 */
	public function getPrefix()
	{}

	/**
	 * @param $part int
	 * @param $value
	 * @return void
	 */
	public function setPrefixPart($part, $value)
	{}
}
