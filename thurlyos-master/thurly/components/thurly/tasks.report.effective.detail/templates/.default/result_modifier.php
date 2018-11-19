<?php
use Thurly\Main\Localization\Loc;
//region TITLE
$APPLICATION->SetPageProperty("title", Loc::getMessage('TASKS_EFFECTIVE_TITLE_FULL', array('#USER_NAME#'=>htmlspecialcharsbx($arResult['USER_NAME']))));
$APPLICATION->SetTitle(Loc::getMessage('TASKS_EFFECTIVE_TITLE_FULL', array('#USER_NAME#'=>htmlspecialcharsbx($arResult['USER_NAME']))));
//endregion TITLE

if (!function_exists('prepareTaskRowUserBaloonHtml'))
{
	function prepareTaskRowUserBaloonHtml($userId, $taskId, $pathToUserProfile)
	{
		$users = \Thurly\Tasks\Util\User::getData(array($userId));
		$user = $users[$userId];
		$user['NAME'] = \Thurly\Tasks\Util\User::getUserName($userId);

		$user['AVATAR'] = \Thurly\Tasks\UI::getAvatar($user['PERSONAL_PHOTO'], 100, 100);
		$user['IS_EXTERNAL'] = \Thurly\Tasks\Util\User::isExternalUser($user['ID']);
		$user['IS_CRM'] = array_key_exists('UF_USER_CRM_ENTITY', $user) && !empty($user['UF_USER_CRM_ENTITY']);

		$user['URL'] = CComponentEngine::MakePathFromTemplate($pathToUserProfile,array("user_id" => $userId));
		$user['URL'] = $user['IS_EXTERNAL']
			? \Thurly\Tasks\Integration\Socialnetwork\Task::addContextToURL($user['URL'],$taskId)
			: $user['URL'];

		// $arParams['USER']['IS_EXTERNAL']   = true || false
		$userIcon = '';
		if ($user['IS_EXTERNAL'])
		{
			$userIcon = 'tasks-grid-avatar-extranet';
		}
		if ($user["EXTERNAL_AUTH_ID"] == 'email')
		{
			$userIcon = 'tasks-grid-avatar-mail';
		}
		if ($user["IS_CRM"])
		{
			$userIcon = 'tasks-grid-avatar-crm';
		}

		$userAvatar = 'tasks-grid-avatar-empty';
		if ($user['AVATAR'])
		{
			$userAvatar = '';
		}

		$userName = '<span class="tasks-grid-avatar  '.$userAvatar.' '.$userIcon.'" 
			'.($user['AVATAR'] ? 'style="background-image: url(\''.$user['AVATAR'].'\')"' : '').'></span>';

		$userName .= '<span class="tasks-grid-username-inner '.$userIcon.'">'.$user['NAME'].'</span>';

		return '<div class="tasks-grid-username-wrapper"><a href="'.$user['URL'].'" class="tasks-grid-username">'.
			   $userName.
			   '</a></div>';
	}
}

$arResult['ROWS'] = array();

if($arResult['VIOLATION_LIST'])
{
	foreach ($arResult['VIOLATION_LIST'] as $item)
	{
		$groupLink = CComponentEngine::MakePathFromTemplate(
			$arParams['PATH_TO_GROUP_LIST'],
			array(
				"user_id" => \Thurly\Tasks\Util\User::getId(),
				"group_id" => $item["GROUP_ID"]
			)
		);

		$taskLink = CComponentEngine::MakePathFromTemplate(
			$arParams['PATH_TO_USER_TASKS_TASK'],
			array(
				"user_id" => \Thurly\Tasks\Util\User::getId(),
				"task_id" => $item["TASK_ID"],
				'action' => 'view'
			)
		);

		switch($item['USER_TYPE'])
		{
			default: $item['USER_TYPE']='-';break;
			case 'A': $item['USER_TYPE']= GetMessage('TASKS_USER_TYPE_A');break;
			case 'R': $item['USER_TYPE']= GetMessage('TASKS_USER_TYPE_R');;break;
		}

		$item['GROUP'] = '<a href="'.$groupLink.'">'.htmlspecialcharsbx($item['GROUP_NAME']).'</a>';

		$item['GROUP'] = '<a href="'.$groupLink.'">'.htmlspecialcharsbx($item['GROUP_NAME']).'</a>';
		$item['TASK'] = '<a href="'.
						$taskLink.
						'">'.
						htmlspecialcharsbx($item['TASK_TITLE']).
						'</a> '.($item['TASK_ZOMBIE'] == 'Y' ? '<em>'.GetMessage('TASKS_EFFECTIVE_DETAIL_DELETED').'</em>' : '');
		$item['ORIGINATOR'] = prepareTaskRowUserBaloonHtml($item['TASK_ORIGINATOR_ID'], $item['TASK_ID'], $arParams['PATH_TO_USER_PROFILE']);

		$item['DEADLINE'] = $item['DEADLINE'] ? \Thurly\Tasks\Util\Type\DateTime::createFromUserTime($item['DEADLINE'])
			: '';
		$item['DATE'] = $item['DATE'] ? formatDateTasks(
			\Thurly\Tasks\Util\Type\DateTime::createFromUserTime($item['DATE'])
		) : '';
		$item['DATE_REPAIR'] = $item['DATE_REPAIR'] ? formatDateTasks(
			\Thurly\Tasks\Util\Type\DateTime::createFromUserTime(
			$item['DATE_REPAIR']
			)
		) : '';

		if (!$item['DATE_REPAIR'])
		{
			$item['DATE_REPAIR'] = GetMessage('TASKS_VIOLATION_NOT_REPAIR');
		}
		
		$rowItem = array(
			"id" => $item["ID"],
			'columns' => $item
		);

		$arResult['ROWS'][] = $rowItem;
	}
}

$arResult['TEMPLATE_DATA'] = array(
	// contains data generated in result_modifier.php
);

function formatDateTasks($date)
{
	$curTimeFormat = "HH:MI:SS";
	$format = 'j F';
	if (LANGUAGE_ID == "en")
	{
		$format = "F j";
	}
	if (LANGUAGE_ID == "de")
	{
		$format = "j. F";
	}

	if (date('Y') != date('Y', strtotime($date)))
	{
		if (LANGUAGE_ID == "en")
		{
			$format .= ",";
		}

		$format .= ' Y';
	}

	$rsSite = CSite::GetByID(SITE_ID);
	if ($arSite = $rsSite->Fetch())
	{
		$curDateFormat = $arSite["FORMAT_DATE"];
		$curTimeFormat = str_replace($curDateFormat." ", "", $arSite["FORMAT_DATETIME"]);
	}

	if ($curTimeFormat == "HH:MI:SS")
	{
		$currentDateTimeFormat = " G:i";
	}
	else //($curTimeFormat == "H:MI:SS TT")
	{
		$currentDateTimeFormat = " g:i a";
	}

	if (date('Hi', strtotime($date)) > 0)
	{
		$format .= ', '.$currentDateTimeFormat;
	}

	$str = (!$date
		? GetMessage('TASKS_NOT_PRESENT')
		: \Thurly\Tasks\UI::formatDateTime(
			MakeTimeStamp($date),
			$format
		));

	return $str;
}