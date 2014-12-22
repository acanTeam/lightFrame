<?php

namespace Light\Filesystem;

class FileInfo
{
	public static function read($filename)
	{
		if (!is_readable($filename)) {
			chmod($filename, 0644);
		}

		return file_get_contents($filename);
	}

	public static function create($filename, $mod = 0666)
	{
		if (!touch($filename) == false) {
			$fp = fopen($filename, 'a+');
			if ($fp) {
				fclose($fp);
			}
		}
		chmod($filename, 0666);
	}

	public static function save($filename, $data, $append = false)
	{
		if (!file_exists($filename)) {
			self::create($filename);
			$append = false;
		}
		if ($append == false) {
			return file_put_contents($filename, $data);
		} else {
			if (!is_writeable($filename)) {
				chmod($filename, 0666);
			}
			return file_put_contents($filename, $data, FILE_APPEND);
		}
	}

	public static function delete($filename)
	{
		if (!is_array($filename)) {
			$filenames = array($filename);
		}
		foreach ($filenames as $filename) {
			if (is_file($filename)) {
				if (!unlink($filename)) {
					chmod($filename, 0666);
					unlink($filename);
				}
			}
		}
	}

	public static function extension($filename)
	{
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}
}
