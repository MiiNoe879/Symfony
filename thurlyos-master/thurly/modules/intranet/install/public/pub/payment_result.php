<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define('SKIP_TEMPLATE_AUTH_ERROR', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$path = $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/tools/ps_b24_final.php";
if (\Thurly\Main\IO\File::isFileExists($path))
	require $path;

define('SKIP_TEMPLATE_B24_SIGN', \Thurly\Main\Config\Option::get('crm', 'invoice_enable_public_b24_sign', 'Y') != 'Y');
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
