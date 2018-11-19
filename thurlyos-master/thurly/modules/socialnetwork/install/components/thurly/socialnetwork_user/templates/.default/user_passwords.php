<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$pageId = "user";
include("util_menu.php");

if (isset($arResult["VARIABLES"]["user_id"]) && $USER->GetID() !== $arResult["VARIABLES"]["user_id"])
{
	ShowError(GetMessage("SONET_PASS_ACCESS_ERROR"));
	return;
}

$APPLICATION->IncludeComponent("thurly:main.app.passwords", "", array());
?>
<?
if (IsModuleInstalled("intranet"))
{
	$request = Thurly\Main\Context::getCurrent()->getRequest();
	$downloadUrl = "http://dl.thurlyos.com/b24/thurlyos_desktop.exe";
	if (stripos($request->getUserAgent(), "Macintosh") !== false)
	{
		$downloadUrl = "http://dl.thurlyos.com/b24/thurlyos_desktop.dmg";
	}
	?>
	<div class="bx-apps-attached-block">
		<span class="bx-apps-icon download"></span> <a href="<?=$downloadUrl?>" style="margin-right: 20px;text-transform: uppercase;"><?=GetMessage("main_app_pass_desktop")?></a>
		<?=GetMessage("main_app_pass_mobile")?>
		<span class="bx-apps-icon iOS"></span> <a href="https://itunes.apple.com/<?=\Thurly\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID)?>/app/thurlyos/id561683423?l=ru&ls=1&mt=8">iOS</a>
		<span class="bx-apps-icon android"></span> <a href="https://play.google.com/store/apps/details?id=com.thurlyos.android">android</a>
	</div>
<?
}
?>