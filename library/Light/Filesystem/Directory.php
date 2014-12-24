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

    public static function getTree($directory)
    {
        $infos = array();
        if (!is_dir($directory)) {
            return $infos;
        }

        $baseKey = basename($directory);
        $directory .= substr($directory, -1) == '/' ? '' : '/';
        $subInfos = glob($directory . '*');
        foreach ($subInfos as $subInfo) {
            if (is_dir($subInfo)) {
                $infos[basename($subInfo)] = self::getTree($subInfo);
            } else {
                $infos['_files'][] = $subInfo;
            }
        }
        return $infos;
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

	public static function mkdir($dir, $mode = 0755)
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

