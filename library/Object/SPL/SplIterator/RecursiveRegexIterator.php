<?php
namespace SplIterator;

use PreDefine/RecursiveIterator;

class RecursiveRegexIterator extends RegexIterator implements RecursiveIterator
{
	/**
	 * @param $iterator RecursiveIterator
	 * @param $regex string
	 * @param $mode int
	 * @param $flags int
	 * @param $preg_falgs int
	 * @return void
	 */
	public function __construct($iterator, $regex, $mode = self::MATCH, $flags = 0, $preg_falgs = 0)
	{}
}
