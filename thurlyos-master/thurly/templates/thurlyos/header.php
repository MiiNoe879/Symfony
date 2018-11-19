<?
use Thurly\Intranet\Integration\Templates\ThurlyOS\ThemePicker;
use Thurly\Main\Config\Option;
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Main\ModuleManager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

//Ajax Performance Optimization
if (isset($_GET["RELOAD"]) && $_GET["RELOAD"] == "Y")
{
	return; //Live Feed Ajax
}
else if (strpos($_SERVER["REQUEST_URI"], "/historyget/") > 0)
{
	return;
}
else if (isset($_GET["IFRAME"]) && $_GET["IFRAME"] === "Y" && !isset($_GET["SONET"]))
{
	//For the task iframe popup
	$APPLICATION->SetPageProperty("BodyClass", "task-iframe-popup");
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/thurlyos.js", true);
	return;
}

CModule::IncludeModule("intranet");
CJSCore::Init("sidepanel_thurlyos");

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"]."/thurly/templates/".SITE_TEMPLATE_ID."/header.php");

$APPLICATION->GroupModuleJS("timeman","im");
$APPLICATION->GroupModuleJS("webrtc","im");
$APPLICATION->GroupModuleJS("pull","im");
$APPLICATION->GroupModuleCSS("timeman","im");
$APPLICATION->MoveJSToBody("im");
$APPLICATION->MoveJSToBody("timeman");
$APPLICATION->SetUniqueJS('bx24', 'template');
$APPLICATION->SetUniqueCSS('bx24', 'template');

$isCompositeMode = defined("USE_HTML_STATIC_CACHE");

$isIndexPage =
	$APPLICATION->GetCurPage(true) === SITE_DIR."stream/index.php" ||
	$APPLICATION->GetCurPage(true) === SITE_DIR."index.php" ||
	(defined("THURLY24_INDEX_PAGE") && constant("THURLY_INDEX_PAGE") === true)
;

$isThurlyOSCloud = ModuleManager::isModuleInstalled("thurlyos");

if ($isIndexPage)
{
	if (!defined("THURLY24_INDEX_PAGE"))
	{
		define("THURLY24_INDEX_PAGE", true);
	}

	if ($isCompositeMode)
	{
		define("THURLY24_INDEX_COMPOSITE", true);
	}
}

function showJsTitle()
{
	$GLOBALS["APPLICATION"]->AddBufferContent("getJsTitle");
}

function getJsTitle()
{
	$title = $GLOBALS["APPLICATION"]->GetTitle("title", true);
	$title = html_entity_decode($title, ENT_QUOTES, SITE_CHARSET);
	$title = CUtil::JSEscape($title);
	return $title;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=1135">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?if ($isThurlyOSCloud):?>
<meta name="apple-itunes-app" content="app-id=561683423" />
<link rel="apple-touch-icon-precomposed" href="/images/iphone/57x57.png" />
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/iphone/72x72.png" />
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/iphone/114x114.png" />
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/iphone/144x144.png" />
<?endif;

$APPLICATION->ShowHead(false);
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/thurlyos.js", true);

ThemePicker::getInstance()->showHeadAssets();

$bodyClass = "template-thurlyos";
if ($isIndexPage)
{
	$bodyClass .= " no-paddings start-page";
}

$bodyClass .= " thurlyos-".ThemePicker::getInstance()->getCurrentBaseThemeId()."-theme";
?>
<title><? if (!$isCompositeMode || $isIndexPage) $APPLICATION->ShowTitle()?></title>
</head>
<body class="<?=$bodyClass?>">
<?
ThemePicker::getInstance()->showBodyAssets();

if ($isCompositeMode && !$isIndexPage)
{
	$frame = new \Thurly\Main\Page\FrameStatic("title");
	$frame->startDynamicArea();
	?><script type="text/javascript">document.title = "<?showJsTitle()?>";</script><?
	$frame->finishDynamicArea();
}

$isExtranet =
	ModuleManager::isModuleInstalled("extranet") &&
	COption::GetOptionString("extranet", "extranet_site") === SITE_ID
;

$APPLICATION->ShowViewContent("im-fullscreen");
?>
<table class="bx-layout-table">
	<tr>
		<td class="bx-layout-header">
			<? if ((!$isThurlyOSCloud || $USER->IsAdmin()) && !defined("SKIP_SHOW_PANEL")):?>
				<div id="panel">
				<?$APPLICATION->ShowPanel();?>
				</div>
			<? endif ?>
<?
if ($isThurlyOSCloud)
{
	if (Option::get('thurlyos', 'creator_confirmed', 'N') !== 'Y')
	{
		$APPLICATION->IncludeComponent(
			'thurly:thurlyos.creatorconfirmed',
			'',
			array(),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}

	if (
		Option::get("thurlyos", "domain_changed", 'N') === 'N' ||
		is_array(\CUserOptions::GetOption('thurlyos', 'domain_changed', false))
	)
	{
		CJSCore::Init(array('b24_rename'));
	}
}
?>
			<div id="header">
				<div id="header-inner">
					<?
					//This component was used for menu-create-but.
					//We have to include the component before thurly:timeman for composite mode.
					if (CModule::IncludeModule('tasks') && CBXFeatures::IsFeatureEnabled('Tasks')):
						$APPLICATION->IncludeComponent(
							"thurly:tasks.iframe.popup",
							".default",
							array(
								"ON_TASK_ADDED" => "#SHOW_ADDED_TASK_DETAIL#",
								"ON_TASK_CHANGED" => "BX.DoNothing",
								"ON_TASK_DELETED" => "BX.DoNothing"
							),
							null,
							array("HIDE_ICONS" => "Y")
						);
					endif;

					if (!$isExtranet)
					{
						if (!ModuleManager::isModuleInstalled("timeman") ||
							!$APPLICATION->IncludeComponent('thurly:timeman', 'thurlyos', array(), false, array("HIDE_ICONS" => "Y" ))
						)
						{
							$APPLICATION->IncludeComponent('thurly:planner', 'thurlyos', array(), false, array("HIDE_ICONS" => "Y" ));
						}
					}
					else
					{
						CJSCore::Init("timer");?>
						<div class="timeman-wrap">
							<span id="timeman-block" class="timeman-block">
								<span class="bx-time" id="timeman-timer"></span>
							</span>
						</div>
						<script type="text/javascript">BX.ready(function() {
							BX.timer.registerFormat("thurlyos_time", B24.Timemanager.formatCurrentTime);
							BX.timer({
								container: BX("timeman-timer"),
								display : "thurlyos_time"
							});
						});</script>
					<?
					}
					?>
					<!--suppress CheckValidXmlInScriptTagBody -->
					<script type="text/javascript" data-skip-moving="true">
						(function() {
							var isAmPmMode = <?=(IsAmPmMode() ? "true" : "false") ?>;
							var time = document.getElementById("timeman-timer");
							var hours = new Date().getHours();
							var minutes = new Date().getMinutes();
							if (time)
							{
								time.innerHTML = formatTime(hours, minutes, 0, isAmPmMode);
							}
							else if (document.addEventListener)
							{
								document.addEventListener("DOMContentLoaded", function() {
									time.innerHTML = formatTime(hours, minutes, 0, isAmPmMode);
								});
							}

							function formatTime(hours, minutes, seconds, isAmPmMode)
							{
								var ampm = "";
								if (isAmPmMode)
								{

									ampm = hours >= 12 ? "PM" : "AM";
									ampm = '<span class="time-am-pm">' + ampm + '</span>';
									hours = hours % 12;
									hours = hours ? hours : 12;
								}
								else
								{
									hours = hours < 10 ? "0" + hours : hours;
								}

								return	'<span class="time-hours">' + hours + '</span>' + '<span class="time-semicolon">:</span>' +
									'<span class="time-minutes">' + (minutes < 10 ? "0" + minutes : minutes) + '</span>' + ampm;
							}
						})();
					</script>
					<div class="header-logo-block">
						<?$APPLICATION->ShowViewContent("sitemap"); ?>
						<span class="header-logo-block-util"></span>
						<?
						$clientLogo = COption::GetOptionInt("thurlyos", "client_logo", "");
						if (Loader::includeModule("thurlyos"))
						{
							$licenseType = CThurlyOS::getLicenseType();
							if (!in_array($licenseType, array("team", "company", "nfr", "edu", "demo")))
							{
								$clientLogo = "";
							}
						}
						$siteTitle = trim(COption::GetOptionString("thurlyos", "site_title", ""));

						if (file_exists($_SERVER["DOCUMENT_ROOT"].SITE_DIR."include/company_name.php") && !$clientLogo && !$siteTitle)
						{
							$logoID = COption::GetOptionString("main", "wizard_site_logo", "", SITE_ID);
							?><a id="logo_24_a" href="<?=SITE_DIR?>" title="<?=GetMessage("THURLY24_LOGO_TOOLTIP")?>" class="logo">
								<?if ($logoID):?>
									<span class="logo-img-span">
										<?$APPLICATION->IncludeComponent("thurly:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/company_name.php"), false);?>
									</span>
								<?else:
									?><span id="logo_24_text"><?
										?><span class="logo-text"><?=htmlspecialcharsbx(COption::GetOptionString("main", "site_name", ""));?></span><?
										?><span class="logo-color">Group</span><?
									?></span>
								<?endif?>
							</a>
						<?
						}
						else
						{
							?>
							<a id="logo_24_a" href="<?=SITE_DIR?>" title="<?=GetMessage("THURLY24_LOGO_TOOLTIP")?>" class="logo"><?
								if(strlen($siteTitle) <= 0)
								{
									$siteTitle = $isThurlyOSCloud ? GetMessage('THURLY24_SITE_TITLE_DEFAULT') : COption::GetOptionString("main", "site_name", "");
								}
								?>
								<span id="logo_24_text" <?if ($clientLogo):?>style="display:none"<?endif?>>
									<span class="logo-text"><?=htmlspecialcharsbx($siteTitle)?></span><?
									if(COption::GetOptionString("thurlyos", "logo24show", "Y") !=="N"):?><span class="logo-color">Group</span><?endif?>
								</span>
								<span class="logo-img-span">
									<img id="logo_24_img" src="<?if ($clientLogo) echo CFile::GetPath($clientLogo)?>" <?if (!$clientLogo):?>style="display:none;"<?endif?>/>
								</span>
								<?
								if (Loader::includeModule("thurlyos") && \CThurlyOS::IsPortalAdmin($USER->GetID()))
								{
									if (!\CThurlyOS::isDomainChanged()):
										CJSCore::Init("spotlight");
									?>
										<div class="header-logo-block-settings header-logo-block-settings-show">
											<span
												id="b24_rename_button" class="header-logo-block-settings-item"
												onclick="BX.ThurlyOS.renamePortal(BX('b24_rename_button')); return false;"
												title="<?=GetMessage('THURLY24_SETTINGS_TITLE')?>"></span>
										</div>
									<?else:?>
										<div class="header-logo-block-settings">
											<span
												id="b24_rename_button" class="header-logo-block-settings-item"
												onclick="location.href='<?=CThurlyOS::PATH_CONFIGS?>'; return false;"
												title="<?=GetMessage('THURLY24_SETTINGS_TITLE_RENAMED')?>"></span>
										</div>
									<?endif;

									$showDomainSpotLite = CUserOptions::GetOption("thurlyos", "show_domain_spotlight");
									if (
										!\CThurlyOS::isDomainChanged()
										&& (
											isset($_SESSION['B24_SHOW_RENAME_POPUP_HINT'])
											|| isset($showDomainSpotLiteOption["time"]) && time() > $showDomainSpotLiteOption["time"]
										)
									):
										unset($_SESSION['B24_SHOW_RENAME_POPUP_HINT']);
									?>
										<script>
											BX.ready(function ()
											{
												if (!!BX.ThurlyOS && !!BX.ThurlyOS.renamePortal)
												{
													BX.ThurlyOS.renamePortal.showNotify(BX("b24_rename_button"))
												}
											})
										</script>
										<?
									elseif(isset($_GET['b24renameform'])):
									?>
										<script>
											BX.ready(function()
											{
												if(!!BX.ThurlyOS && !!BX.ThurlyOS.renamePortal)
												{
													BX.ThurlyOS.renamePortal()
												}
											})
										</script>
									<?
									endif;

								}
								?>
							</a>
							<?
						}
						?>
					</div>

					<?$APPLICATION->IncludeComponent("thurly:search.title", ".default", Array(
						"NUM_CATEGORIES" => "5",
						"TOP_COUNT" => "5",
						"CHECK_DATES" => "N",
						"SHOW_OTHERS" => "Y",
						"PAGE" => "#SITE_DIR#search/index.php",
						"CATEGORY_0_TITLE" => GetMessage("THURLY24_SEARCH_EMPLOYEE"),
						"CATEGORY_0" => array(
							0 => "custom_users",
						),
						"CATEGORY_1_TITLE" => GetMessage("THURLY24_SEARCH_GROUP"),
						"CATEGORY_1" => array(
							0 => "custom_sonetgroups",
						),
						"CATEGORY_2_TITLE" => GetMessage("THURLY24_SEARCH_MENUITEMS"),
						"CATEGORY_2" => array(
							0 => "custom_menuitems",
						),
						"CATEGORY_3_TITLE" => "CRM",
						"CATEGORY_3" => array(
							0 => "crm",
						),
						"CATEGORY_4_TITLE" => GetMessage("THURLY24_SEARCH_MICROBLOG"),
						"CATEGORY_4" => array(
							0 => "microblog", 1 => "blog",
						),
						"CATEGORY_OTHERS_TITLE" => GetMessage("THURLY24_SEARCH_OTHER"),
						"SHOW_INPUT" => "N",
						"INPUT_ID" => "search-textbox-input",
						"CONTAINER_ID" => "search",
						"USE_LANGUAGE_GUESS" => (LANGUAGE_ID == "ru") ? "Y" : "N"
						),
						false
					);

					$profileLink = $isExtranet ? SITE_DIR."contacts/personal" : SITE_DIR."company/personal";
					$APPLICATION->IncludeComponent(
						"thurly:system.auth.form",
						"",
						array(
							"PATH_TO_SONET_PROFILE" => $profileLink."/user/#user_id#/",
							"PATH_TO_SONET_PROFILE_EDIT" => $profileLink."/user/#user_id#/edit/",
							"PATH_TO_SONET_EXTMAIL_SETUP" => $profileLink."/mail/?config",
							"PATH_TO_SONET_EXTMAIL_MANAGE" => $profileLink."/mail/manage/"
						),
						false
					);?>
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td class="bx-layout-cont">
		<?
			$leftColumnClass = "";
			if (CUserOptions::GetOption("intranet", "left_menu_collapsed") === "Y")
			{
				$leftColumnClass .= " menu-collapsed-mode";
			}

			$imBarExists =
				CModule::IncludeModule("im") &&
				CBXFeatures::IsFeatureEnabled("WebMessenger") &&
				!defined("BX_IM_FULLSCREEN")
			;

			if ($imBarExists)
			{
				$leftColumnClass .= " im-bar-mode";
			}
		?>
			<table class="bx-layout-inner-table<?=$leftColumnClass?>">
				<tr class="bx-layout-inner-top-row">
					<td class="bx-layout-inner-left" id="layout-left-column">
						<?$APPLICATION->IncludeComponent(
							"thurly:menu",
							"left_vertical",
							array(
								"ROOT_MENU_TYPE" => file_exists($_SERVER["DOCUMENT_ROOT"].SITE_DIR.".superleft.menu_ext.php") ? "superleft" : "top",
								"CHILD_MENU_TYPE" => "left",
								"MENU_CACHE_TYPE" => "Y",
								"MENU_CACHE_TIME" => "604800",
								"MENU_CACHE_USE_GROUPS" => "N",
								"MENU_CACHE_USE_USERS" => "Y",
								"CACHE_SELECTED_ITEMS" => "N",
								"MENU_CACHE_GET_VARS" => array(),
								"MAX_LEVEL" => $isExtranet ? "1" : "2",
								"USE_EXT" => "Y",
								"DELAY" => "N",
								"ALLOW_MULTI_SELECT" => "N"
							),
							false
						);

						if ($imBarExists)
						{
							//This component changes user counters on the page.
							//User counters can be changed in the left menu (left_vertical template).
							$APPLICATION->IncludeComponent(
								"thurly:im.messenger",
								"",
								array(
									"CONTEXT" => "POPUP-FULLSCREEN",
									"RECENT" => "Y",
									"PATH_TO_SONET_EXTMAIL" => SITE_DIR."company/personal/mail/"
								),
								false,
								array("HIDE_ICONS" => "Y")
							);
						}
						?>

						<div id="feed-up-btn-wrap" class="feed-up-btn-wrap" title="<?=GetMessage("THURLY24_UP")?>" onclick="B24.goUp();">
							<div class="feed-up-btn">
								<span class="feed-up-text"><?=GetMessage("THURLY24_UP")?></span>
								<span class="feed-up-btn-icon"></span>
							</div>
						</div>
					</td>
					<td class="bx-layout-inner-center" id="content-table">
					<?
					if ($isCompositeMode && !$isIndexPage)
					{
						$isDefaultTheme = ThemePicker::getInstance()->getCurrentThemeId() === "default";
						$bodyClass = $isDefaultTheme ? "" : " no-background";
						$dynamicArea = new \Thurly\Main\Page\FrameStatic("workarea");
						$dynamicArea->setAssetMode(\Thurly\Main\Page\AssetMode::STANDARD);
						$dynamicArea->setContainerId("content-table");
						$dynamicArea->setStub('
							<table class="bx-layout-inner-inner-table composite-mode'.$bodyClass.'">
								<colgroup>
									<col class="bx-layout-inner-inner-cont">
								</colgroup>
								<tr class="bx-layout-inner-inner-top-row">
									<td class="bx-layout-inner-inner-cont">
										<div class="pagetitle-wrap"></div>
									</td>
								</tr>
								<tr>
									<td class="bx-layout-inner-inner-cont">
										<div id="workarea">
											<div id="workarea-content">
												<div class="workarea-content-paddings">
													<div style="position: relative; height: 50vh;">
														<div class="intranet-loader-container" id="b24-loader">
															<svg class="intranet-loader-circular" viewBox="25 25 50 50">
																<circle class="intranet-loader-path" 
																	cx="50" cy="50" r="20" fill="none" 
																	stroke-miterlimit="10"
																/>
															</svg>
														</div>
													</div>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</table>
							<script>B24.showLoading();</script>'
						);
						$dynamicArea->startDynamicArea();
					}
					?>
						<table class="bx-layout-inner-inner-table <?$APPLICATION->ShowProperty("BodyClass");?>">
							<colgroup>
								<col class="bx-layout-inner-inner-cont">
							</colgroup>
							<?if (!$isIndexPage):?>
							<tr class="bx-layout-inner-inner-top-row">
								<td class="bx-layout-inner-inner-cont">
									<div class="page-header">
										<?
										$APPLICATION->ShowViewContent("above_pagetitle");
										$APPLICATION->IncludeComponent(
											"thurly:menu",
											"top_horizontal",
											array(
												"ROOT_MENU_TYPE" => "left",
												"MENU_CACHE_TYPE" => "N",
												"MENU_CACHE_TIME" => "604800",
												"MENU_CACHE_USE_GROUPS" => "N",
												"MENU_CACHE_USE_USERS" => "Y",
												"CACHE_SELECTED_ITEMS" => "N",
												"MENU_CACHE_GET_VARS" => array(),
												"MAX_LEVEL" => "1",
												"USE_EXT" => "Y",
												"DELAY" => "N",
												"ALLOW_MULTI_SELECT" => "N"
											),
											false
										);
										?>

										<div class="pagetitle-wrap">
											<div class="pagetitle-inner-container">
												<div class="pagetitle-menu pagetitle-container pagetitle-last-item-in-a-row" id="pagetitle-menu"><?
													if ($isThurlyOSCloud):
														$GLOBALS['INTRANET_TOOLBAR']->Disable();
													else:
														$GLOBALS['INTRANET_TOOLBAR']->Enable();
														$GLOBALS['INTRANET_TOOLBAR']->Show();
													endif;
													$APPLICATION->ShowViewContent("pagetitle")
													?></div>
												<div class="pagetitle">
													<span id="pagetitle" class="pagetitle-item"><?$APPLICATION->ShowTitle(false);?></span>
													<span class="pagetitle-star" id="pagetitle-star"></span>
												</div>
												<?$APPLICATION->ShowViewContent("inside_pagetitle")?>
											</div>
										</div>
										<div class="pagetitle-below"><?$APPLICATION->ShowViewContent("below_pagetitle")?></div>
									</div>
								</td>
							</tr>
							<?endif?>
							<tr>
								<td class="bx-layout-inner-inner-cont">

									<div id="workarea">
										<?if($APPLICATION->GetProperty("HIDE_SIDEBAR", "N") != "Y"):
											?><div id="sidebar"><?
											$APPLICATION->ShowViewContent("sidebar");
											$APPLICATION->ShowViewContent("sidebar_tools_1");
											$APPLICATION->ShowViewContent("sidebar_tools_2");
											?></div>
										<?endif?>
										<div id="workarea-content">
											<div class="workarea-content-paddings">
											<?$APPLICATION->ShowViewContent("topblock")?>
											<?if ($isIndexPage):?>
												<div class="pagetitle-wrap">
													<div class="pagetitle-inner-container">
														<div class="pagetitle-menu" id="pagetitle-menu"><?$APPLICATION->ShowViewContent("pagetitle")?></div>
														<div class="pagetitle" id="pagetitle"><?$APPLICATION->ShowTitle(false);?></div>
														<?$APPLICATION->ShowViewContent("inside_pagetitle")?>
													</div>
												</div>
											<?endif?>
											<?CPageOption::SetOptionString("main.interface", "use_themes", "N"); //For grids?>