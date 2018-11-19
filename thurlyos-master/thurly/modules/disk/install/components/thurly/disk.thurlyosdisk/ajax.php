<?php

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID'])? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('disk'))
{
	return;
}

class DiskThurlyOSDiskController extends \Thurly\Disk\Internals\Controller
{
	protected function listActions()
	{
		return array(
			'default' => array(
				'method' => array('POST'),
			),
		);
	}

	protected function processActionDefault()
	{
		if($this->request->getPost('installDisk'))
		{
			\Thurly\Disk\Desktop::setDesktopDiskInstalled();
			$this->sendJsonSuccessResponse();
		}
		if($this->request->getPost('uninstallDisk'))
		{
			\Thurly\Disk\Desktop::setDesktopDiskUninstalled();
			$this->sendJsonSuccessResponse();
		}
		if($this->request->getPost('reInstallDisk'))
		{
			\CUserOptions::setOption('disk', 'DesktopDiskReInstall', true, false, $this->getUser()->getId());
			\Thurly\Disk\Desktop::setDesktopDiskInstalled();

			$this->sendJsonSuccessResponse();
		}
	}

}
$action = empty($_GET['action'])? 'default' : $_GET['action'];
$controller = new DiskThurlyOSDiskController();
$controller
	->setActionName($action)
	->exec()
;

