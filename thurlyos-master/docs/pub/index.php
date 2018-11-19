<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");?>
<?

	if(\Thurly\Main\Config\Option::get("main", "site_stopped", "N") === 'Y')
	{
		return;
	}

	if(\Thurly\Main\Config\Option::get('disk', 'successfully_converted', false) != 'Y')
	{
		$APPLICATION->IncludeComponent("thurly:webdav.extlinks", ".default", Array());
		return;
	}
?>

<?$APPLICATION->IncludeComponent("thurly:disk.external.link", ".default", Array());?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");?>