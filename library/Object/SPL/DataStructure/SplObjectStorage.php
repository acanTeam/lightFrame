<?php
namespace DataStructure;

use PreDefine/Countable;
use PreDefine/Iterator;
use PreDefine/Serializable;
use PreDefine/ArrayAccess;

class SplObjectStorage implements Countable, Iterator, ArrayAccess, Serializable
{
	/**
	 * @param $storage
	 * @return void
	 */
	public function addAll($storage)
	{}

	/**
	 * @param $object object
	 * @param $data mixed
	 * @return void
	 */
	public function attach($object, $data = null)
	{}

	/**
	 * @param $object object
	 * @return void
	 */
	public function detach($object)
	{}

	/**
	 * @param $object object
	 * @return boolean
	 */
	public function contains($object)
	{}

	/**
	 * @param $object string
	 * @return string
	 */
	public function getHash($ojbect)
	{}

	/**
	 * @return mixed
	 */
	public function getInfo()
	{}

	/**
	 * @param $data mixed
	 * @return void
	 */
	public function setInfo($data)
	{}

	/**
	 * @param $storage SplObjectStorage
	 * @return void
	 */
	public function removeAll($storage)
	{}

	/**
	 * @param $storage SplObjectStorage
	 * @return void
	 */
	public function removeAllExcept($storage)
	{}
}
