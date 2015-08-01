<?php

class LiamW_XenForoUpdater_Helper
{
	/**
	 * Recursively copy a directory from source to destination.
	 *
	 * @param string    $source       The source directory.
	 * @param string    $destination  The target directory.
	 * @param bool|true $resetOpcache If true, the opcached version of a copied file will be invalidated once copied.
	 *
	 * @return bool
	 */
	public static function recursiveCopy($source, $destination, $resetOpcache = true)
	{
		if (!is_dir($destination))
		{
			if (!XenForo_Helper_File::createDirectory($destination))
			{
				return false;
			}
		}

		$dir = new DirectoryIterator($source);
		foreach ($dir as $dirInfo)
		{
			if ($dirInfo->isFile())
			{
				copy($dirInfo->getRealPath(), $destination . '/' . $dirInfo->getFilename());
				if ($resetOpcache)
				{
					self::opcacheInvalidateFile($destination . '/' . $dirInfo->getFilename());
				}
			}
			else if (!$dirInfo->isDot() && $dirInfo->isDir())
			{
				self::recursiveCopy($dirInfo->getRealPath(), $destination . '/' . $dirInfo);
			}
		}

		return true;
	}

	/**
	 * Reset the opcache for a file if the invalidate function exists.
	 *
	 * @param string $file The file to reset.
	 */
	public static function opcacheInvalidateFile($file)
	{
		@opcache_invalidate($file, true);
	}
}