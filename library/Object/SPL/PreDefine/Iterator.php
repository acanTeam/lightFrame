<?php
namespace PreDefine;

interface Iterator extends Traversable 
{
	/**
	 * @return mixed
	 */
	abstract public function current();

	/**
	 * @return scalar
	 */
	abstract public function key();

	/**
	 * @return void
	 */
	abstract public function next();

	/**
	 * @return void
	 */
	abstract public function rewind();

	/**
	 * @return boolean
	 */
	abstract public function valid();
}
