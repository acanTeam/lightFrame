<?php
namespace SplFile;

class SplFileInfo
{
	/**
	 * @param $file_name
	 * @return void
	 */
	public function __construct($file_name)
	{}

	/**
	 * @return int
	 */
	public function getATime()
	{}

	/**
	 * @param $suffix string
	 * @return string
	 */
	public function getBasename($suffix = '')
	{}

	/**
	 * @return int
	 */
	public function getCTime()
	{}

	/**
	 * @return string
	 */
	public function getExtension()
	{}

	/**
	 * @param $class_name string
	 * @return SplFileInfo
	 */
	public function getFileInfo($class_name = '')
	{}

	/**
	 * @return string 
	 */
	public function getFilename()
	{}

	/**
	 * @return int
	 */
	public function getGroup()
	{}

	/**
	 * @return int
	 */
	public function getInode()
	{}

	/**
	 * @return string
	 */
	public function getLinkTarget()
	{}

	/**
	 * @return int
	 */
	public function getMTime()
	{}

	/**
	 * @return int
	 */
	public function getOwner()
	{}

	/**
	 * @return string
	 */
	public function getPath()
	{}

	/**
	 * @param $class_name string
	 * @return SplFileInfo
	 */
	public function getPathInfo($class_name = '')
	{}

	/**
	 * @return string
	 */
	public function getPathname()
	{}

	/**
	 * @return int
	 */
	public function getPerms()
	{}

	/**
	 * @return string
	 */
	public function getRealPath()
	{}

	/**
	 * @return int
	 */
	public function getSize()
	{}

	/**
	 * @return string
	 */
	public function getType()
	{}

	/**
	 * @return bool
	 */
	public function isDir()
	{}

	/**
	 * @return bool
	 */
	public function isExecutable()
	{}

	/**
	 * @return bool
	 */
	public function isFile()
	{}

	/**
	 * @return bool
	 */
	public function isLink()
	{}

	/**
	 * @return bool
	 */
	public function isReadable()
	{}

	/**
	 * @return bool
	 */
	public function isWritable()
	{}

	/**
	 * @param $open_mode string
	 * @param $use_include_path bool
	 * @param $context resource
	 * @return SplFileObject
	 */
	public function openFile($open_mode = 'r', $use_include_path = false, $context = null)
	{}

	/**
	 * @param $class_name strin
	 * @return void
	 */
	public function setFileClass($class_name)
	{}

	/**
	 * @param $class_name string
	 * @return void
	 */
	public function setInfoClass($class_name)
	{}

	/**
	 * @return void
	 */
	public function __toString()
	{}
}
