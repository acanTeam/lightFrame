<?php
namespace PreDefine;

interface Serializable 
{
	/**
	 * @return string
	 */
	abstract public function serialize();

	/**
	 * @param $serialized string
	 * @return mixed
	 */
	abstract public function unserialize($serialized);
}
