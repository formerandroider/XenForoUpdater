<?php

class LiamW_XenForoUpdater_Installer
{
	protected function _canBeInstalled(&$error)
	{
		if (XenForo_Application::$versionId < 1020070)
		{
			$error = 'This add-on requires XenForo 1.2.0 or above. Please upgrade XenForo (manually) before installing.';

			return false;
		}

		$hashErrors = XenForo_Helper_Hash::compareHashes(LiamW_XenForoUpdater_FileSums::getHashes());

		if ($hashErrors)
		{
			$error = "The following files could not be found or contain unexpected contents: <ul>";

			foreach ($hashErrors AS $file => $fileError)
			{
				$error .= "<li>$file - " . ($fileError == 'mismatch' ? 'File contains unexpected contents' : 'File not found') . "</li>";
			}

			$error .= "</ul>";

			return false;
		}

		if (!LiamW_XenForoUpdater_Helper::zipInstalled())
		{
			$error = 'The ZIP extension is required for this add-on to work. Please ask your host to recompile PHP with the <a href="http://php.net/manual/en/book.zip.php" target="_blank"><i>zip</i></a> extension.';

			return false;
		}

		return true;
	}

	public static function install($installedAddon, $addonData)
	{
		if (!self::_canBeInstalled($error))
		{
			throw new XenForo_Exception($error, true);
		}
	}

	public static function uninstall()
	{
		// Not yet used.
	}
}