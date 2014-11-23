<?php
namespace PreDefine;

class ArrayObject implements Countable, ArrayAccess, Serializable, IteratorAggregate
{
	const STD_PROP_LIST = 1;
	const ARRAY_AS_PROPS = 2;

	/**
	 * @param $input mixed
	 * @param $flag int
	 * @param $iterator_class string
	 * @return void
	 */
	public function __construct($input, $flags = 1, $iterator_class = 'Iterator')
	{}

	/**
	 * @param $value mixed
	 * @return void
	 */
	public function append($value)
	{}

	/**
	 * @return void
	 */
	public function asort()
	{}

	/**
	 * @return void
	 */
	public function ksort()
	{}

	/**
	 * @return void
	 */
	public function natcasesort()
	{}

	/**
	 * @param $cmp_function callable
	 * @return void
	 */
	public function uasort($cmp_function)
	{}

	/**
	 * @param $cmp_function callable
	 * @return void
	 */
	public function uksort($cmp_function)
	{}

	/**
	 * @param $input mixed
	 * @return array
	 */
	public function exchangeArray($input)
	{}

	/**
	 * @return array
	 */
	public function getArrayCopy()
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
	 * @return string
	 */
	public function getIteratorClass()
	{}

	/**
	 * @param $iterator_class string
	 * @return void
	 */
	public function setIteratorClass($iterator_class)
	{}
}
