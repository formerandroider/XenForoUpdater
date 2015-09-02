<?php

class LiamW_XenForoUpdater_CronEntry_CleanUp
{
	public static function cleanUp()
	{
		/** @var LiamW_XenForoUpdater_Model_AutoUpdate $autoUpdateModel */
		$autoUpdateModel = XenForo_Model::create('LiamW_XenForoUpdater_Model_AutoUpdate');

		$options = XenForo_Application::getOptions();

		switch ($options->liam_xenforoupdater_autopurge)
		{
			case 'all_cron':
				$autoUpdateModel->purgeAllData();
				break;
			case 'zip_cron':
				$autoUpdateModel->purgeZips();
				break;
			case 'dir_cron':
				$autoUpdateModel->purgeDirs();
				break;
		}
	}
}