<?
use Thurly\Intranet\Integration\Templates\ThurlyOS\ThemePicker;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!$USER->IsAuthorized())
{
?>
	<div class="authorization-block"><a href="<?=(SITE_DIR."auth/?backurl=".$arResult["BACKURL"])?>" class="authorization-text"><?=GetMessage("AUTH_AUTH")?></a></div>
<?
	return;
}

$videoSteps = array(
	array(
		"id" => "start",
		"patterns" => array(),
		"learning_path" => "/start/",
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_1"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_1"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_1")
	),
	array(
		"id" => "tasks",
		"learning_path" => "/tasks/",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/tasks/~",
			"~^".SITE_DIR."workgroups/group/\\d+/tasks/~"
		),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_2"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_2"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_2")
	),
	array(
		"id" => "calendar",
		"learning_path" => "/calendar/",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/calendar/~",
			"~^".SITE_DIR."workgroups/group/\\d+/calendar/~"
		),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_3"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_3"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_3")
	),
	array(
		"id" => "docs",
		"learning_path" => "/docs/",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/disk/~",
			"~^".SITE_DIR."docs/~",
			"~^".SITE_DIR."workgroups/group/\\d+/disk/~"
		),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_4"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_4"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_4")
	),
	array(
		"id" => "crm",
		"learning_path" => "/crm/",
		"patterns" => array("~^".SITE_DIR."crm/~"),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_14"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_14"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_14")
	)
);

if (LANGUAGE_ID == "ru" || LANGUAGE_ID == "ua")
{
	$videoSteps[] = array(
		"id" => "company_struct",
		"learning_path" => "/company/vis_structure.php",
		"patterns" => $USER->CanDoOperation("thurlyos_invite") ? array("~^".SITE_DIR."company/vis_structure.php~") : array(),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_13"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_13"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_13")
	);

	$videoSteps[] = array(
		"id" => "marketplace",
		"learning_path" => "/marketplace/",
		"patterns" => array("~^".SITE_DIR."marketplace/~"),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_15"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_15"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_15")
	);

	$videoSteps[] = array(
		"id" => "im",
		"learning_path" => "",
		"patterns" => array(),
		"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_16"),
		"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_16"),
		"youtube" => GetMessage("THURLY24_HELP_VIDEO_16")
	);
}
else
{
	$addVideo = array(
		"5" => array("crm_import", array(), "/crm/import/"),
		"6" => array("crm_email", array(), "/crm/email/"),
		"7" => array("crm_perms", array("~^".SITE_DIR."crm/configs/perms/~"), "/crm/configs/perms/"),
		"8" => array("crm_lists", array("~^".SITE_DIR."crm/configs/status/~"), "/crm/lists/"),
		"9" => array("crm_bp", array("~^".SITE_DIR."crm/configs/bp/~"), "/crm/configs/bp/"),
		"10" => array("im", array(), "/im/"),
		"11" => array("lists", array("~^".SITE_DIR."company/lists/~"), "/company/lists/"),
		"12" => array("twitter", array(), "/twitter/")
	);

	foreach ($addVideo as $number => $ids)
	{
		$videoSteps[] = array(
			"id" => $ids[0],
			"patterns" => $ids[1],
			"learning_path" => $ids[2],
			"title" => GetMessage("THURLY24_HELP_VIDEO_TITLE_".$number),
			"title_full" => GetMessage("THURLY24_HELP_VIDEO_TITLE_FULL_".$number),
			"youtube" => GetMessage("THURLY24_HELP_VIDEO_".$number)
		);
	}
}

?>

<script type="text/javascript">
	function showUserMenu()
	{
		var bindElement = BX("user-block");
		BX.addClass(bindElement, "user-block-active");
		BX.PopupMenu.show("user-menu", bindElement, [
			{ text : "<?=GetMessageJS("AUTH_PROFILE")?>", className : "menu-popup-no-icon", href : "<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SONET_PROFILE'], array("user_id" => $USER->GetID() ))?>"},
			{ text : "<?=GetMessageJS("AUTH_CHANGE_PROFILE")?>", className : "menu-popup-no-icon", href : "<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SONET_PROFILE_EDIT'], array("user_id" => $USER->GetID() ))?>"},
			<? if (ThemePicker::isAvailable()): ?>
			{
				text : "<?=GetMessageJS("AUTH_THEME_DIALOG")?>",
				className : "menu-popup-no-icon",
				id: "theme-picker",
				onclick: function() {
					BX.Intranet.ThurlyOS.ThemePicker.Singleton.showDialog(false);
				}
			},
			<? endif ?>
			<?if(isset($arResult['B24NET_WWW'])):?>
			{ text : "<?=GetMessageJS("AUTH_PROFILE_B24NET")?>", className : "menu-popup-no-icon", href : "<?=CUtil::JSEscape($arResult['B24NET_WWW'])?>"},
			<?endif;?>
			<?if (IsModuleInstalled("im")):?>
			{ text : "<?=GetMessageJS("AUTH_CHANGE_NOTIFY")?>", className : "menu-popup-no-icon", onclick : "BXIM.openSettings({'onlyPanel':'notify'})"},
			<?endif?>
			<?if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable()):?>
			{ text : "<?=GetMessageJS("AUTH_CHANGE_MAIL")?>", className : "menu-popup-no-icon", href : "<?=CUtil::JSEscape($arParams['PATH_TO_SONET_EXTMAIL_SETUP']); ?>" },
				<?if (is_object($USER) && $USER->IsAuthorized() && ($USER->isAdmin() || $USER->canDoOperation('thurlyos_config'))):?>
					<?if (IsModuleInstalled('thurlyos') || in_array(LANGUAGE_ID, array('ru', 'ua'))):?>
			{ text : "<?=GetMessageJS("AUTH_MANAGE_MAIL")?>", className : "menu-popup-no-icon", href : "<?=CUtil::JSEscape($arParams['PATH_TO_SONET_EXTMAIL_MANAGE']); ?>" },
					<?endif?>
				<?endif?>
			<?endif?>
			<?if (!IsModuleInstalled('thurlyos') && $USER->isAdmin()):?>
			{ text : "<?=GetMessageJS("AUTH_ADMIN_SECTION")?>", className : "menu-popup-no-icon", href : "/thurly/admin/"},
			<?endif?>
			{ text : "<?=GetMessageJS("AUTH_LOGOUT")?>", className : "menu-popup-no-icon", href : "/auth/?logout=yes&backurl=" + encodeURIComponent(B24.getBackUrl()) }
		],
			{
				offsetTop: 9,
				offsetLeft: 43,
				angle: true,
				events: {
					onPopupClose : function() {
						BX.removeClass(this.bindElement, "user-block-active");
					}
				}
			});
	}
</script>

<?// spotlight
if ($arResult["SHOW_USER_INFO_SPOTLIGHT"])
{
	CJSCore::Init("spotlight");
?>
	<form method="post" enctype="multipart/form-data" style="display: none; margin: 10px 10px 0 10px;" id="userSpotLightForm">
		<div class="user-spotlight-title"><?=GetMessage("AUTH_POPUP_TITLE")?></div>
		<label class="user-spotlight-form-label"><?=GetMessage("AUTH_POPUP_FIRSTNAME")?></label>
		<input type="text" name="spotlightFirstName" id="spotlightFirstName" class="user-spotlight-form-input">
		<br/><br/>
		<label class="user-spotlight-form-label"><?=GetMessage("AUTH_POPUP_LASTNAME")?></label>
		<input type="text" name="spotlightLastName" id="spotlightLastName" class="user-spotlight-form-input">
		<br/><br/>
		<?
		$APPLICATION->IncludeComponent('thurly:main.file.input', '', array(
			'INPUT_NAME' => 'spotlightPhotoId',
			'INPUT_NAME_UNSAVED' => 'SPOTLIGHT_PHOTO_ID_UNSAVED',
			'CONTROL_ID' => 'SPOTLIGHT_PHOTO_IMAGE_ID',
			'INPUT_VALUE' => "",
			'MULTIPLE' => 'N',
			'ALLOW_UPLOAD' => 'I',
			'INPUT_CAPTION' => GetMessage("AUTH_POPUP_PHOTO"),
			'SHOW_AVATAR_EDITOR' => 'Y'
		));
		?>
		<hr class="user-spotlight-hr">
	</form>

	<script>
	BX.ready(function() {
		BX.message({
			AUTH_SAVE_BUTTON: '<?=GetMessageJS('AUTH_SAVE_BUTTON')?>',
			AUTH_DELAY_BUTTON: '<?=GetMessageJS('AUTH_DELAY_BUTTON')?>'
		});

		BX.Intranet.SystemAuthForm.showUserInfoSpotLight('<?=$this->GetFolder()."/ajax.php"?>');
	});
	</script>
<?
}
?>

<?
$arViewedSteps = CUserOptions::GetOption("thurlyos", "help_views", array());
$currentStepId = __getStepByUrl($videoSteps, $APPLICATION->GetCurDir());

if (!in_array("start", $arViewedSteps))
{
	$currentStepId = "start";
}

require_once($_SERVER["DOCUMENT_ROOT"].$this->GetFolder()."/functions.php");
CIntranetPopupShow::getInstance()->init(($currentStepId && !in_array($currentStepId, $arViewedSteps) ? "Y" : "N"));

AddEventHandler("intranet", "OnIntranetPopupShow", "onIntranetPopupShow");
if (!function_exists("onIntranetPopupShow"))
{
	function onIntranetPopupShow()
	{
		$isPopupShowed = CIntranetPopupShow::getInstance()->isPopupShowed();
		if ($isPopupShowed == "Y")
			return false;
	}
}
?>

<div class="user-block" id="user-block" onclick="showUserMenu()">
	<span class="user-img user-default-avatar" <?if ($arResult["USER_PERSONAL_PHOTO_SRC"]):?>style="background: url('<?=$arResult["USER_PERSONAL_PHOTO_SRC"]?>') no-repeat center; background-size: cover;"<?endif?>></span><span class="user-name" id="user-name"><?if (!$arResult["SHOW_LICENSE_BUTTON"]):?><?=$arResult["USER_NAME"]?><?endif?></span>
</div>
<?
if ($arResult["SHOW_LICENSE_BUTTON"]):?>
<?
	$arJsParams = array(
		"LICENSE_PATH" => $arResult["B24_LICENSE_PATH"],
		"COUNTER_URL" => $arResult["LICENSE_BUTTON_COUNTER_URL"],
		"HOST" => $arResult["HOST_NAME"]
	);
?>
<a href="javascript:void(0)" onclick="BX.Intranet.SystemAuthForm.licenseHandler(<?=CUtil::PhpToJSObject($arJsParams)?>)" class="upgrade-btn <?if (!isset($_SESSION["B24_LICENSE_BUTTON"])) echo " upgrade-btn-anim"; if (!in_array(LANGUAGE_ID, array("ru", "ua"))) echo " upgrade-btn-en"?>">
	<span class="upgrade-btn-icon"></span>
	<span class="upgrade-btn-text"><?=GetMessage("B24_LICENSE_ALL")?></span>
</a>
<?endif?>
<div class="help-block" id="bx-help-block" title="<?=GetMessage("AUTH_HELP")?>">
	<div class="help-icon-border"></div>
	<div class="help-block-icon"></div>
	<div class="help-block-counter-wrap" id="bx-help-notify">
		<?if (false && isset($arResult["HELP_NOTIFY_NUM"]) && intval($arResult["HELP_NOTIFY_NUM"])):?>
		<div class="help-block-counter"><?=$arResult["HELP_NOTIFY_NUM"]?></div>
		<?endif?>
	</div>
</div>

<?$frame = $this->createFrame("b24_helper")->begin("");?>
	<?
	$support_bot = 0;
	if (CModule::IncludeModule("imbot"))
	{
		if (method_exists('\\Thurly\\ImBot\\Bot\\Support', 'isEnabled') && \Thurly\ImBot\Bot\Support::isEnabled())
			$support_bot = \Thurly\ImBot\Bot\Support::getBotId();
	}

	CJSCore::Init(array('helper'));
	$helpUrl = GetMessage("B24_HELPDESK_URL")."/widget2/";
	$helpUrl = CHTTP::urlAddParams($helpUrl, array(
			"url" => urlencode("http://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam()),
			"is_admin" => IsModuleInstalled("thurlyos") && CThurlyOS::IsPortalAdmin($USER->GetID()) || !IsModuleInstalled("thurlyos") && $USER->IsAdmin() ? 1 : 0,
			"user_id" => $USER->GetID(),
			"tariff" => COption::GetOptionString("main", "~controller_group_name", ""),
			"is_cloud" => IsModuleInstalled("thurlyos") ? "1" : "0",
			"support_bot" => $support_bot,
		)
	);

	$frameOpenUrl = CHTTP::urlAddParams($helpUrl, array(
			"action" => "open",
		)
	);
	$frameCloseUrl = CHTTP::urlAddParams($helpUrl, array(
			"action" => "close",
		)
	);

	$host = IsModuleInstalled("thurlyos") ? BX24_HOST_NAME : CIntranetUtils::getHostName();
	$notifyData = array(
		"support_bot" => $support_bot,
		"is_admin" => IsModuleInstalled("thurlyos") && CThurlyOS::IsPortalAdmin($USER->GetID()) || !IsModuleInstalled("thurlyos") && $USER->IsAdmin() ? 1 : 0,
		"user_id" => $USER->GetID(),
		"tariff" => COption::GetOptionString("main", "~controller_group_name", ""),
		"host" => $host,
		"key" => IsModuleInstalled("thurlyos") ? CThurlyOS::RequestSign($host.$USER->GetID()) : md5($host.$USER->GetID().'BX_USER_CHECK'),
		"is_cloud" => IsModuleInstalled("thurlyos") ? "1" : "0",
		"user_date_register" => $arResult["USER_DATE_REGISTER"],
		"portal_date_register" => IsModuleInstalled("thurlyos") ? COption::GetOptionString("main", "~controller_date_create", "") : "",
		"partner_link" => COption::GetOptionString("thurlyos", "partner_id", 0) ? 'Y' : 'N',
		"counter_update_date" => $arResult["COUNTER_UPDATE_DATE"]
	);
	?>

	<?if ($arResult["SHOW_ANIMATED_FINGER_HERO"] == "Y"):?>
	<script>
		BX.Helper.showAnimatedHero(); //with finger
	</script>
	<?endif?>

	<script>
		BX.message({
			HELPER_LOADER: '<?=GetMessageJS('B24_HELP_LOADER')?>',
			HELPER_TITLE: '<?=GetMessageJS('B24_HELP_TITLE_NEW')?>'
		});
		BX.Helper.init({
			frameOpenUrl : '<?=CUtil::JSEscape($frameOpenUrl)?>',
			helpBtn : BX('bx-help-block'),
			notifyBlock : BX('bx-help-notify'),
			langId: '<?=LANGUAGE_ID?>',
			ajaxUrl: '<?=$this->GetFolder()."/ajax.php"?>',
			currentStepId: '<?=CUtil::JSEscape($arResult["CURRENT_STEP_ID"])?>',
			needCheckNotify: '<?=($arResult["NEED_CHECK_HELP_NOTIFICATION"] == "Y" ? "Y" : "N")?>',
			notifyNum: '<?=CUtil::JSEscape($arResult["HELP_NOTIFY_NUM"])?>',
			notifyData: <?=CUtil::PhpToJSObject($notifyData)?>,
			notifyUrl: '<?=GetMessageJS("B24_HELPDESK_URL")."/widget2/notify.php"?>',
			helpUrl: '<?=GetMessageJS("B24_HELPDESK_URL")?>',
			runtimeUrl: '//helpdesk.thurlyos.ru/widget/hero/runtime.js'
		});
		<?
		if ($support_bot && $_REQUEST['support_chat'])
			echo 'BX.addCustomEvent("onImInit", function(BXIM) {BXIM.openMessenger('.$support_bot.');});';
		?>
	</script>
<?$frame->end();?>
