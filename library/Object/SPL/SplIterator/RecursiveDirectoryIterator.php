<?php
namespace SplIterator;

use PreDefine/RecursiveIterator;

class RecursiveDirectoryIterator extends FilesystemIterator implements RecursiveIterator
{
	/**
	 * @param $path string
	 * @param $flags int
	 * @return void
	 */
	public function __construct($path, $flags = FilesystemIterator::KEY_AS_PATHNAME)
	{}

	/**
	 * @return string
	 */
	public function getSubPath()
	{}

	/**
	 * @return string
	 */
	public function getSubPathname()
	{}
}
