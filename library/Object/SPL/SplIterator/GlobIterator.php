<?php
namespace SplIterator;

use PreDefine/Countable;

class GlobIterator extends FilesystemIterator implements Countable
{
	/**
	 * @param $path string
	 * @param $flags int
	 * @return void
	 */
	public function __construct($path, $flags = FilesystemIterator::KEY_AS_PATHNAME)
	{}
}
