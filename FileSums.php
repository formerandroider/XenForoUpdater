<?php

class LiamW_XenForoUpdater_FileSums
{
	public static function addHashes(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
	{
		$hashes += self::getHashes();
	}

	public static function getHashes()
	{
		return array(
			'library/LiamW/XenForoUpdater/Extend/ControllerAdmin/Tools.php' => '4d2696393e3a29b4f3777b35119e55a1',
			'library/LiamW/XenForoUpdater/FtpClient/FtpClient.php' => 'f9e83ba318205eba46b301bc6b075c7b',
			'library/LiamW/XenForoUpdater/FtpClient/FtpException.php' => '6f5218e65c6637591a638e31fb463e84',
			'library/LiamW/XenForoUpdater/FtpClient/FtpWrapper.php' => 'b049dd9d2d8dc186f4e61287949f312c',
			'library/LiamW/XenForoUpdater/Helper.php' => '89e7a30d39bb8f8f02aa8ea9fc74465f',
			'library/LiamW/XenForoUpdater/Installer.php' => '541730716bb4e7b2fd37a2938d1327ec',
			'library/LiamW/XenForoUpdater/Listener.php' => '971f43608aa65892e8502ef7c23e0fe7',
			'library/LiamW/XenForoUpdater/Model/AutoUpdate.php' => '035cecc869d2fc5450a7751386834ed4',
		);
	}
}