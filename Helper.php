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
	 * Recursively delete data from a directory, and then the directory itself.
	 *
	 * @param string $source     The directory to recursively delete.
	 * @param array  $extensions A list of extensions to delete. If this is set, directories <i>will not</i> be deleted.
	 *
	 * @return bool
	 */
	public static function recursiveDelete($source, $extensions = array())
	{
		if (!is_dir($source))
		{
			return true;
		}

		$dir = new DirectoryIterator($source);
		foreach ($dir as $dirInfo)
		{
			if ($extensions && (!in_array($dirInfo->getExtension(), $extensions) || $dirInfo->isDir()))
			{
				continue;
			}

			if (self::isEmptyDir($dirInfo->getRealPath()))
			{
				rmdir($dirInfo->getRealPath());
			}
			else if ($dirInfo->isFile())
			{
				unlink($dirInfo->getRealPath());
			}
			else if (!$dirInfo->isDot() && $dirInfo->isDir())
			{
				self::recursiveDelete($dirInfo->getRealPath());
			}
		}

		@rmdir($source);

		return true;
	}

	public static function isEmptyDir($directory)
	{
		if (!is_dir($directory))
		{
			return false;
		}

		foreach (new DirectoryIterator($directory) as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}

			return false;
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
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($file, true);
		}
	}

	public static function zipInstalled()
	{
		return extension_loaded('zip');
	}
}