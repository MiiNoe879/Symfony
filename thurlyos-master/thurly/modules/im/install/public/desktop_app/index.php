<?
require($_SERVER["DOCUMENT_ROOT"]."/desktop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

\Thurly\Main\Page\Asset::getInstance()->setJsToBody(false);

if (!CModule::IncludeModule('im'))
	return;

if (intval($USER->GetID()) <= 0 || \Thurly\Im\User::getInstance()->isConnector())
{
	?>
<script type="text/javascript">
	if (typeof(BXDesktopSystem) != 'undefined')
		BXDesktopSystem.Login({});
	else
		location.href = '/';
</script><?
	return true;
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/im/install/public/desktop_app/index.php");

if (isset($_GET['IFRAME']) == 'Y')
{
	$APPLICATION->IncludeComponent("thurly:im.messenger", "iframe", Array(
		"CONTEXT" => "FULLSCREEN",
	), false, Array("HIDE_ICONS" => "Y"));
}
else if (!isset($_GET['BXD_API_VERSION']) && strpos($_SERVER['HTTP_USER_AGENT'], 'ThurlyDesktop') === false)
{
	$APPLICATION->IncludeComponent("thurly:im.messenger", "fullscreen", Array(
		"CONTEXT" => "FULLSCREEN",
		"DESIGN" => "DESKTOP",
	), false, Array("HIDE_ICONS" => "Y"));
}
else
{
	?>
	<script type="text/javascript">
		if (typeof(BXDesktopSystem) != 'undefined')
			BX.desktop.init();
		<?if (!isset($_GET['BXD_MODE'])):?>
		else
			location.href = '/';
		<?endif;?>
	</script>
	<?
	$APPLICATION->IncludeComponent("thurly:im.messenger", "", Array(
		"CONTEXT" => "DESKTOP"
	), false, Array("HIDE_ICONS" => "Y"));

	$diskEnabled = false;
	if(IsModuleInstalled('disk'))
	{
		$diskEnabled =
			\Thurly\Main\Config\Option::get('disk', 'successfully_converted', false) &&
			CModule::includeModule('disk');
		if($diskEnabled && \Thurly\Disk\Configuration::REVISION_API >= 5)
		{
			$APPLICATION->IncludeComponent('thurly:disk.thurlyosdisk', '', array('AJAX_PATH' => '/desktop_app/disk.ajax.new.php'), false, Array("HIDE_ICONS" => "Y"));
		}
		else
		{
			$diskEnabled = false;
		}
	}
	if(!$diskEnabled && IsModuleInstalled('webdav'))
	{
		$APPLICATION->IncludeComponent('thurly:webdav.disk', '', array('AJAX_PATH' => '/desktop_app/disk.ajax.php'), false, Array("HIDE_ICONS" => "Y"));
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
