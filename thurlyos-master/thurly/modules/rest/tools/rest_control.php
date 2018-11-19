<?php
require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/prolog_before.php");

/**
 * Thurly vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$result = array();
$request = Thurly\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_thurly_sessid())
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowCSS();
	$APPLICATION->ShowHeadScripts();

	switch($request['control'])
	{
		case 'user_selector':

			$APPLICATION->IncludeComponent(
				"thurly:intranet.user.selector.new",
				".default",
				array(
					"MULTIPLE" => isset($request['mult']) ? 'Y' : 'N',
					"NAME" => $request['name'],
					"VALUE" => array(),
					"POPUP" => "Y",
					"ON_CHANGE" => $request['onchange'],
					"SITE_ID" => $request['site_id'],
					"SHOW_EXTRANET_USERS" => "NONE",
				),
				null,
				array("HIDE_ICONS" => "Y")
			);

		break;

		case 'access_selector':

			echo \CJSCore::Init(array('access'), true);

		break;

		case 'crm_selector':

			$APPLICATION->IncludeComponent(
				'thurly:crm.entity.selector.ajax',
				'.default',
				array(
					"MULTIPLE" => $request['multiple'] == 'Y' ? 'Y' : 'N',
					'VALUE' => $request['value'],
					'ENTITY_TYPE' => $request['entityType'],
					'NAME' => 'restCrmSelector',
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);

		break;
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");
die();
