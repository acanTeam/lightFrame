<?php

namespace Light\Filesystem;

class Directory
{
	public static function read($dirname, $recursive = false)
	{
		static $allInfo;
		$dirname .= subStr($dirname, -1) == '/' ? '' : '/';
		$dirInfo = glob($dirname . '*');
		if ($recursive == false) {
			return $dirInfo;
		} else {
			foreach ($dirInfo as $info) {
				if (is_dir($info)) {
					if (!is_readable($info)) {
						chmod($info, 0777);
					}
					$allInfo[] = $info;
					self::read($info, true);
				} else {
					$allInfo[] = $info;
				}
			}
		}
		return $allInfo;
	}

	public static function rmdir($dirname)
	{
		if (is_dir($dirname) && !is_writeable($dirname)) {
			if (!chmod($dirname, 0666)) {
				return false;
			}
		} elseif (!is_dir($dirname)) {
			return false;
		}
		$dirname .= subStr($dirname, -1) == '/' ? '' : '/';
		$dirInfo = glob($dirname . '*');
		foreach ($dirInfo as $info) {
			if (is_dir($info)) {
				self::rmdir($info);
			} else {
				unlink($info);
			}
		}
		@rmdir($dirname);
	}

	public function mkdir($dir, $mode = 0777)
	{
		if (!is_dir($dir)) {
			$ret = @mkdir($dir, $mode, true);
			if (!$ret) {
				exit('function:mkdir failed');
			}
		}
		return true;
	}
}

