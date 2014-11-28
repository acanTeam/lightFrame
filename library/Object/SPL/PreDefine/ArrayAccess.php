<?php
namespace PreDefine;

interface ArrayAccess
{
	/**
	 * @param $offset mixed 
	 * @return boolean
	 */
	abstract public function offsetExists($offset);

	/**
	 * @param $offset mixed
	 * @return mixed
	 */
	abstract public function offsetGet($offset);

	/**
	 * @param $offset mixed
	 * @param $value mixed
	 * @return void
	 */
	abstract public function offsetSet($offset, $value);

	/**
	 * @param $offset mixed
	 * @return void
	 */
	abstract public function offsetUnset($offset);
}
