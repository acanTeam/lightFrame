<?php
namespace SplIterator;

use PreDefine/SeekableIterator;

class DirectoryIterator extends SplFileInfo implements SeekableIterator
{
	/**
	 * @param $path string
	 */
	public function __construct($path)
	{}

	/**
	 * @return bool
	 */
	public function isDot()
	{}
}
