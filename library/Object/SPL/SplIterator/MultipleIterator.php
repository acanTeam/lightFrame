<?php
namespace SplIterator;

use PreDefine/Iterator;

class MultipleIterator implements Iterator
{
	const MIT_NEED_ANY = 0;
	const MIT_NEED_ALL = 1;
	const MIT_KEYS_NUMNERIC = 0;
	const MIT_KEYS_ASSOC = 2;

	/**
	 * @param $flags int
	 * @return void
	 */
	public function __construct($flags = MultipleIterator::MIT_NEED_ALL)
	{}

	/**
	 * @param $iterator Iterator
	 * @param $infos string
	 * @return void
	 */
	public function attachIterator($iterator, $infos = '')
	{}

	/**
	 * @param $iterator
	 * @return void
	 */
	public function detachIterator($iterator)
	{}

	/**
	 * @return void
	 */
	public function countIterators()
	{}

	/**
	 * @param $iterator
	 * @return void
	 */
	public function containsIterator($iterator)
	{}

	/**
	 * @return void
	 */
	public function getFlags()
	{}

	/**
	 * @param $flags int
	 * @return void
	 */
	public function setFlags($flags)
	{}
}
