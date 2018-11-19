<? if (!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 *
 * @var $APPLICATION CAllMain
 * @var $USER CAllUser
 * @var $params array
 */
global $APPLICATION, $USER;
use Thurly\Main;
use Thurly\Main\Authentication\ApplicationPasswordTable;


if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
{
	header('Access-Control-Allow-Methods: POST, OPTIONS');
	header('Access-Control-Max-Age: 60');
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
	die('');
}

if (!IsModuleInstalled('thurlyos'))
{
	header('Access-Control-Allow-Origin: *');
}

$data = array(
	"status" => "failed",
	"thurly_sessid" => thurly_sessid(),
);

$userData = CHTTP::ParseAuthRequest();
$APPLICATION->RestartBuffer();

$login = $userData["basic"]["username"];
$isAlreadyAuthorized = $USER->IsAuthorized();

if (!$isAlreadyAuthorized)
{
	if (IsModuleInstalled('thurlyos'))
	{
		header('Access-Control-Allow-Origin: *');
	}

	if ($login)
	{
		if (\Thurly\Main\Loader::includeModule('thurlyos') && ($captchaInfo = CThurlyOS::getStoredCaptcha()))
		{
			$data["captchaCode"] = $captchaInfo["captchaCode"];
			$data["captchaURL"] = $captchaInfo["captchaURL"];
		}
		elseif ($APPLICATION->NeedCAPTHAForLogin($login))
		{
			$data["captchaCode"] = $APPLICATION->CaptchaGetCode();
		}

		if (\Thurly\Main\Loader::includeModule("security") && \Thurly\Security\Mfa\Otp::isOtpRequired())
		{
			$data["needOtp"] = true;
		}
	}

	if (Main\Loader::includeModule('socialservices'))
	{
		$lastUserStatus = \Thurly\Socialservices\Network::getLastUserStatus();
		if ($lastUserStatus)
		{
			if (is_array($lastUserStatus))
			{
				$data["error"] = $lastUserStatus["error"];
				$data["error_message"] = $lastUserStatus["error_message"];
			}
			else
			{
				$data["error"] = $lastUserStatus;
			}
		}
	}

	CHTTP::SetStatus("401 Unauthorized");
}
else
{
	$isExtranetModuleInstalled = (
		\Thurly\Main\Loader::includeModule("extranet")
		&& CExtranet::GetExtranetSiteID()
	);

	$selectFields = array(
		"FIELDS" => array("PERSONAL_PHOTO")
	);

	if ($isExtranetModuleInstalled)
	{
		$selectFields["SELECT"] = array("UF_DEPARTMENT");
	}

	$dbUser = CUser::GetList(
		($by = array("last_name" => "asc", "name" => "asc")),
		($order = false),
		Array("ID" => $USER->GetID()),
		$selectFields
	);
	$curUser = $dbUser->Fetch();
	$avatarSource = "";

	if (intval($curUser["PERSONAL_PHOTO"]) > 0)
	{
		$avatar = CFile::ResizeImageGet(
			$curUser["PERSONAL_PHOTO"],
			array("width" => 64, "height" => 64),
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		if ($avatar && strlen($avatar["src"]) > 0)
		{
			$avatarSource = $avatar["src"];
		}
	}

	$bExtranetUser = ($isExtranetModuleInstalled && intval($curUser["UF_DEPARTMENT"][0]) <= 0);
	\Thurly\Main\Loader::includeModule("pull");
	if (!$bExtranetUser)
	{
		$isOpenlinesOperator = \Thurly\Main\Loader::includeModule('im') && \Thurly\Im\Integration\Imopenlines\User::isOperator();

		$menuTabs = Array();
		$menuTabs[] = array(
			"sort" => count($menuTabs)+1,
			"imageName" => "chat",
			"badgeCode" => "messages",
			"component" => array(
				"name" => "JSComponentChatRecent",
				"title" => GetMessage("MD_COMPONENT_IM_RECENT"),
				"componentCode" => "im.recent",
				"scriptPath" => \Thurly\Mobile\ComponentManager::getComponentPath("im.recent"),
				"params" => array(
					"COMPONENT_CODE" => "im.recent",
					"USER_ID" => $USER->GetId(),
					"OPENLINES_USER_IS_OPERATOR" => $isOpenlinesOperator,
					"SITE_ID" => SITE_ID,
					"LANGUAGE_ID" => LANGUAGE_ID,
					"LIMIT_ONLINE" => CUser::GetSecondsForLimitOnline(),
					"IM_GENERAL_CHAT_ID" => CIMChat::GetGeneralChatId(),
					"MESSAGES" => Array(
						"COMPONENT_TITLE" => GetMessage("MD_COMPONENT_IM_RECENT"),
						"IMOL_CHAT_ANSWER_M" => \Thurly\Im\Integration\Imopenlines\Localize::get(\Thurly\Im\Integration\Imopenlines\Localize::FILE_LIB_CHAT, "IMOL_CHAT_ANSWER_M"),
						"IMOL_CHAT_ANSWER_F" => \Thurly\Im\Integration\Imopenlines\Localize::get(\Thurly\Im\Integration\Imopenlines\Localize::FILE_LIB_CHAT, "IMOL_CHAT_ANSWER_F")
					)
				),
				"settings" => array("useSearch" => true, "preload" => true),
			),
		);
		if ($isOpenlinesOperator)
		{
			$menuTabs[] = array(
				"sort" => count($menuTabs)+1,
				"imageName" => "openlines",
				"badgeCode" => "openlines",
				"component" => array(
					"name" => "JSComponentChatRecent",
					"title" => GetMessage("MD_COMPONENT_IM_OPENLINES"),
					"componentCode" => "im.openlines.recent",
					"scriptPath" => \Thurly\Mobile\ComponentManager::getComponentPath("im.recent"), // TODO change
					"params" => array(
						"COMPONENT_CODE" => "im.openlines.recent",
						"USER_ID" => $USER->GetId(),
						"OPENLINES_USER_IS_OPERATOR" => $isOpenlinesOperator,
						"SITE_ID" => SITE_ID,
						"LANGUAGE_ID" => LANGUAGE_ID,
						"LIMIT_ONLINE" => CUser::GetSecondsForLimitOnline(),
						"IM_GENERAL_CHAT_ID" => CIMChat::GetGeneralChatId(),
						"MESSAGES" => Array(
							"COMPONENT_TITLE" => GetMessage("MD_COMPONENT_IM_OPENLINES"),
							"IMOL_CHAT_ANSWER_M" => \Thurly\Im\Integration\Imopenlines\Localize::get(\Thurly\Im\Integration\Imopenlines\Localize::FILE_LIB_CHAT, "IMOL_CHAT_ANSWER_M"),
							"IMOL_CHAT_ANSWER_F" => \Thurly\Im\Integration\Imopenlines\Localize::get(\Thurly\Im\Integration\Imopenlines\Localize::FILE_LIB_CHAT, "IMOL_CHAT_ANSWER_F")
						)
					),
					"settings" => array("useSearch" => false, "preload" => true),
				)
			);
		}

		$menuTabs[] = array(
			"sort" => count($menuTabs)+1,
			"imageName" => "stream",
			"badgeCode" => "stream",
			"page" => array("useSlidingNavBar" => true, "url" => $params["START_PAGE"] ? $params["START_PAGE"] : ""),
		);

		$menuTabs[] = array(
			"sort" => count($menuTabs)+1,
			"imageName" => "bell",
			"badgeCode" => "notifications",
			"page" => array("page_id" => "notifications", "url" => "/mobile/im/notify.php")
		);

		if (!$isOpenlinesOperator)
		{
			$menuTabs[] = array(
				"sort" => count($menuTabs)+1,
				"imageName" => "task",
				"page" => array("url" => "/mobile/tasks/snmrouter/?routePage=roles"),
			);
		}
		$menuTabs[] = array(
			"sort" => count($menuTabs)+1,
			"imageName" => "menu_2",
			"component" => array(
				"settings" => array("useSearch" => true),
				"name" => "JSMenuComponent",
				"title" => GetMessage("MD_COMPONENT_MORE"),
				"componentCode" => "settings",
				"scriptPath" => \Thurly\Mobile\ComponentManager::getComponentPath("more"),
				"params" => array(
					"userId" => $USER->getId(),
					"SITE_ID" => SITE_ID,
				)
			)
		);

		$voximplantServer = '';
		$voximplantLogin = '';
		$voximplantLines = array();
		$voximplantDefaultLineId = '';
		if($voximplantInstalled = Main\Loader::includeModule('voximplant'))
		{
			$viUser = new CVoxImplantUser();
			$voximplantAuthorization = $viUser->getAuthorizationInfo($USER->getId());
			if($voximplantAuthorization->isSuccess())
			{
				$voximplantAuthorizationData = $voximplantAuthorization->getData();
				$voximplantServer = $voximplantAuthorizationData['server'];
				$voximplantLogin = $voximplantAuthorizationData['login'];
			}
			$voximplantLines = CVoxImplantConfig::GetLines(true, true);
			$voximplantDefaultLineId = CVoxImplantUser::getUserOutgoingLine($USER->getId());
		}

		$data = array(
			"status" => "success",
			"id" => $USER->GetID(),
			"name" => CUser::FormatName(CSite::GetNameFormat(false), array(
				"NAME" => $USER->GetFirstName(),
				"LAST_NAME" => $USER->GetLastName(),
				"SECOND_NAME" => $USER->GetSecondName(),
				"LOGIN" => $USER->GetLogin()
			)),
			"sessid_md5" => thurly_sessid(),
			"target" => md5($USER->GetID() . CMain::GetServerUniqID()),
			"photoUrl" => $avatarSource,
			"wkWebViewSupported" => true,
			"tabInterfaceSupported" => true,
			"tabs" => $menuTabs,
			"services" => array(
				array(
					"scriptPath" => \Thurly\Mobile\ComponentManager::getComponentPath("calls"),
					"name" => "JSComponent",
					"componentCode" => "calls",
					"params" => array(
						"userId" => $USER->getId(),
						"isAdmin" => $USER->isAdmin(),
						"siteDir" => SITE_DIR,
						"voximplantInstalled" => $voximplantInstalled,
						"voximplantServer" => $voximplantServer,
						"voximplantLogin" => $voximplantLogin,
						"canPerformCalls" => $voximplantInstalled && \Thurly\Voximplant\Security\Helper::canCurrentUserPerformCalls(),
						"lines" => $voximplantLines,
						"defaultLineId" => $voximplantDefaultLineId
					)
				),
				array(
					"scriptPath" => \Thurly\Mobile\ComponentManager::getComponentPath("communication"),
					"params" => array(
						"USER_ID" => $USER->getId(),
						"SITE_ID" => SITE_ID,
						"LANGUAGE_ID" => LANGUAGE_ID,
						"PULL_CONFIG" => \Thurly\Pull\Config::get(Array('JSON' => true))
					),
					"name" => "JSComponent",
					"componentCode" => "communication"
				)
			),
			"useModernStyle" => true,
			"appmap" => Array(
				"main" => Array("url" => $params["START_PAGE"] ? $params["START_PAGE"] : "", "bx24ModernStyle" => true),
				"menu" => Array("url" => $params["MENU_PAGE"] ? $params["MENU_PAGE"] : ""),
				"right" => Array("url" => $params["CHAT_PAGE"]),
				"notification" => array("url" => "/mobile/im/notify.php")
			)
		);
	}
	else
	{
		$rsSites = CSite::GetByID(CExtranet::GetExtranetSiteID());
		if (
			($arExtranetSite = $rsSites->Fetch())
			&& ($arExtranetSite["ACTIVE"] != "N")
		)
		{
			$data["status"] = "success";
			$data["whiteList"] = array($arExtranetSite["DIR"] . "mobile/");
			$data["appmap"] = array(
				"main" => Array("url" => $arExtranetSite["DIR"] . "mobile/index.php", "bx24ModernStyle" => true),
				"menu" => Array("url" => $arExtranetSite["DIR"] . "mobile/left.php"),
				"right" => Array("url" => $arExtranetSite["DIR"] . "mobile/im/right.php")
			);
		}
	}
	$needAppPass = \Thurly\Main\Context::getCurrent()->getServer()->get("HTTP_BX_APP_PASS");
	$appUUID = \Thurly\Main\Context::getCurrent()->getServer()->get("HTTP_BX_APP_UUID");
	$deviceName = \Thurly\Main\Context::getCurrent()->getServer()->get("HTTP_BX_DEVICE_NAME");

	if ($needAppPass == 'mobile' && $USER->GetParam("APPLICATION_ID") === null)
	{
		if (strlen($appUUID) > 0)
		{
			$result = ApplicationPasswordTable::getList(Array(
				'select' => Array('ID'),
				'filter' => Array(
					'USER_ID' => $USER->GetID(),
					'CODE' => $appUUID
				)
			));
			if ($row = $result->fetch())
			{
				ApplicationPasswordTable::delete($row['ID']);
			}
		}

		$password = ApplicationPasswordTable::generatePassword();

		$res = ApplicationPasswordTable::add(array(
			'USER_ID' => $USER->GetID(),
			'APPLICATION_ID' => 'mobile',
			'PASSWORD' => $password,
			'CODE' => $appUUID,
			'DATE_CREATE' => new Main\Type\DateTime(),
			'COMMENT' => GetMessage("MD_GENERATE_BY_MOBILE") . (strlen($deviceName) > 0 ? " (" . $deviceName . ")" : ""),
			'SYSCOMMENT' => GetMessage("MD_MOBILE_APPLICATION")
		));

		if ($res->isSuccess())
		{
			$data["appPassword"] = $password;
		}
	}
}

return Main\Text\Encoding::convertEncoding($data, LANG_CHARSET, 'UTF-8');
