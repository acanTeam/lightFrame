<?php
namespace SplIterator;

abstract FilterIterator extends IteratorIterator
{
	/**
	 * @return bool
	 */
	abstract public function accept();
}
