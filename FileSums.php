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
			'library/LiamW/XenForoUpdater/CronEntry/CleanUp.php' => '3d7704fa65c96fdf63ea073a44df9a33',
			'library/LiamW/XenForoUpdater/Extend/ControllerAdmin/Tools.php' => 'bb79b25b47970a82d850062856057a5a',
			'library/LiamW/XenForoUpdater/FtpClient/FtpClient.php' => 'f9e83ba318205eba46b301bc6b075c7b',
			'library/LiamW/XenForoUpdater/FtpClient/FtpException.php' => '6f5218e65c6637591a638e31fb463e84',
			'library/LiamW/XenForoUpdater/FtpClient/FtpWrapper.php' => 'b049dd9d2d8dc186f4e61287949f312c',
			'library/LiamW/XenForoUpdater/Helper.php' => '16f6cbd82eb3b25e03aa3a3edf43d9d2',
			'library/LiamW/XenForoUpdater/Installer.php' => '8d5953c53b41a0dd7cda6758b34917ed',
			'library/LiamW/XenForoUpdater/Listener.php' => '971f43608aa65892e8502ef7c23e0fe7',
			'library/LiamW/XenForoUpdater/Model/AutoUpdate.php' => '60efc692087cb7388501758d61f284f6',
		);
	}
}