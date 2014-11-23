<?php
namespace PreDefine;

interface SeekableIterator extends Iterator
{
	/**
	 * @param $position int
	 * @return void
	 */
	abstract public function seek($position);
}
