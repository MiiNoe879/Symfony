<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */

/**
 * Thurly vars
 *
 * @global CUser $USER
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

\Thurly\Main\Loader::includeModule('crm');

if($USER->IsAuthorized() && check_thurly_sessid())
{
	CUtil::decodeURIComponent($_POST);
	$guid = isset($_REQUEST['guid']) ? $_REQUEST['guid'] : '';
	if($guid === '')
	{
		echo 'ERROR: GUID IS EMPTY.';
		die();
	}

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	if($action === 'saveconfig')
	{
		$config = isset($_POST['config']) && is_array($_POST['config']) ? $_POST['config'] : array();

		if(isset($_POST['forAllUsers'])
			&& $_POST['forAllUsers'] === 'Y'
			&& CCrmAuthorizationHelper::CanEditOtherSettings()
		)
		{
			if(isset($_POST['delete']) && $_POST['delete'] === 'Y')
			{
				CUserOptions::DeleteOptionsByName('crm.entity.editor', $guid);
			}
			CUserOptions::SetOption('crm.entity.editor', $guid, $config, true);
		}
		CUserOptions::SetOption('crm.entity.editor', $guid, $config);
	}
	elseif($action === 'resetconfig')
	{
		if(isset($_POST['forAllUsers'])
			&& $_POST['forAllUsers'] === 'Y'
			&& CCrmAuthorizationHelper::CanEditOtherSettings()
		)
		{
			CUserOptions::DeleteOptionsByName('crm.entity.editor', $guid);
		}
		else
		{
			CUserOptions::DeleteOption('crm.entity.editor', $guid);
		}
	}
	else
	{
		echo 'ERROR: ACTION IS EMPTY OR NOT SUPPORTED.';
		die();
	}
}
echo 'OK';
