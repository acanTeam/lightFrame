<?php
namespace PreDefine;

interface IteratorAggregate extends Traversable
{
	/**
	 * @return Traversable
	 */
	abstract public function getIterator();
}
