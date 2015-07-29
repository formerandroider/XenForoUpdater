<?php

class LiamW_XenForoUpdater_Listener
{
	public static function extendToolsController($class, array &$extend)
	{
		$extend[] = 'LiamW_XenForoUpdater_Extend_ControllerAdmin_Tools';
	}
}