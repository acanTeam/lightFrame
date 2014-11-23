<?php
namespace SplIterator;

class RegexIterator extends FilterIterator
{
	const MATCH = 0;
	const GET_MATCH = 1;
	const ALL_MATCHES = 2;
	const SPLIT = 3;
	const REPLACE = 4;
	const USE_KEY = 1;

	/**
	 * @param $iterator Iterator
	 * @param $regex string
	 * @param $mode int
	 * @param $flags = int
	 * @param $preg_flags int
	 * @return void
	 */
	public function __construct($iterator, $regex $mode = self::MATCH, $flags = 0, $preg_flags = 0)
	{}

	/**
	 * @return int
	 */
	public function getFlags()
	{}

	/**
	 * @param $flags int
	 * @return void
	 */
	public function setFlags($flags)
	{}

	/**
	 * @return int
	 */
	public function getMode()
	{}

	/**
	 * @param $mode int
	 * @return void
	 */
	public function setMode($mode)
	{}

	/**
	 * @return int
	 */
	public function getPregFlags()
	{}

	/**
	 * @param $preg_flags int
	 * @return void
	 */
	public function setPregFlags($preg_flags)
	{}

	/**
	 * @return string
	 */
	public function getRegex()
	{}
}
