<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

if (!$USER->isAuthorized() && CModule::includeModule('mail'))
	Thurly\Mail\User::login();

?>
<?
$postId = (isset($_REQUEST["post_id"]) ? intval($_REQUEST["post_id"]) : false);
if (IsModuleInstalled('thurlyos'))
{
	GetGlobalID();
	$blogGroupId = $GLOBAL_BLOG_GROUP[SITE_ID];
}
else
{
	$blogGroupId = COption::GetOptionString("socialnetwork", "userbloggroup_id", false, SITE_ID, true);
}
$blogGroupId = ($blogGroupId ? intval($blogGroupId) : false);

$hasAccess = false;
if ($USER->isAuthorized())
{
	$hasAccess = true;

	if (CModule::includeModule('extranet') && !CExtranet::isIntranetUser())
		$hasAccess = false;

	if (isModuleInstalled('mail') || isModuleInstalled('socialservices'))
	{
		$userData = Thurly\Main\UserTable::getList(array(
			'select' => array('ID', 'EXTERNAL_AUTH_ID'),
			'filter' => array(
				'=ID' => $USER->getId()
			)
		))->fetch();

		if (isModuleInstalled('mail') && $userData['EXTERNAL_AUTH_ID'] == 'email')
			$hasAccess = true;

		if (isModuleInstalled('socialservices') && $userData['EXTERNAL_AUTH_ID'] == 'replica')
			$hasAccess = false;
	}
}

if ($postId && $blogGroupId && $hasAccess)
{
	$arComponentParams = Array(
		"ID" => $postId,
		"PUB" => "Y",
		"SEF" => "N",
		"CHECK_PERMISSIONS_DEST" => "Y",
		"FROM_LOG" => "N",
		"GROUP_ID" => $blogGroupId,
		"NAME_TEMPLATE" => CSite::GetNameFormat(),
		"SHOW_LOGIN" => "Y",
		"DATE_TIME_FORMAT" => (
			LANGUAGE_ID == 'en'
				? "j F Y g:i a"
				: (
					LANGUAGE_ID == 'de'
						? "j. F Y, G:i"
						: "j F Y G:i"
			)
		),
		"SHOW_RATING" => "Y",
		"USE_CUT" => "N",
		"ALLOW_POST_CODE" => "N",
		"GET_FOLLOW" => "N",
		"PATH_TO_POST" => SITE_DIR."pub/post.php?post_id=#post_id#",

//		"PATH_TO_USER" => SITE_DIR."pub/user.php?user_id=#user_id#",


		"SET_NAV_CHAIN" => "N",
		"SET_TITLE" => "N",
		"USE_SHARE" => "N",

//		"ADIT_MENU" => $arAditMenu,
		"MARK_NEW_COMMENTS" => "N",
		"IS_HIDDEN" => false,
		"CACHE_TIME" => "A",
		"CACHE_TYPE" => "3600",
		"LAZYLOAD" => "N",
		"RETURN_DATA" => "Y",
		"RETURN_ERROR" => "Y"
	);

	$arReturn = $APPLICATION->IncludeComponent(
		"thurly:socialnetwork.blog.post",
		"",
		$arComponentParams
	);
}
else
{
	$arReturn = array(
		'ERROR_CODE' => !$USER->isAuthorized() ? 'NO_AUTH' : !$blogGroupId ? 'NO_BLOG' : !$postId ? 'NO_POST' : 'NO_RIGHTS'
	);
}


?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>