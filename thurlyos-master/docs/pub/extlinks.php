<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");?>
<?$APPLICATION->IncludeComponent("thurly:webdav.extlinks", ".default", Array());?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");?>