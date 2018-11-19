<?
define("STOP_STATISTICS", true);

define('DLSERVER', 'www.1c-thurly.ru');
define('DLPORT', 80);
define('DLPATH', '/download/files/locations/');
define('DLMETHOD', 'GET');
define('DLZIPFILE', 'zip_ussr.csv');

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/classes/general/wizard.php");

$wizard =  new CWizard("thurly:sale.locations");
$wizard->IncludeWizardLang("scripts/loader.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
{
	echo GetMessage('WSL_LOADER_ERROR_ACCESS_DENIED');
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
	die();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/general/location_import.php");

$arLoadParams = array(
	'STEP' => intval($_REQUEST['STEP']),
	'CSVFILE' => $_REQUEST['CSVFILE'],
	'LOADZIP' => $_REQUEST['LOADZIP'],
	'DLSERVER' => DLSERVER,
	'DLPORT' => DLPORT,
	'DLPATH' => DLPATH,
	'DLMETHOD' => DLMETHOD,
	'DLZIPFILE' => DLZIPFILE
);

$arLoadResult = saleLocationLoadFile($arLoadParams);

if (strlen($arLoadResult['ERROR']) > 0)
{
	echo $arLoadResult['ERROR'];

	if(isset($arLoadResult['RUN_ERROR']) && $arLoadResult['RUN_ERROR'] == true)
		echo '<script>RunError()</script>';
}
elseif (isset($arLoadResult['COMPLETE']) && $arLoadResult['COMPLETE'] === true)
{
	echo GetMessage('WSL_LOADER_ALL_LOADED');
	echo '<script>EnableButton();</script>';
}
elseif ($arLoadResult['STEP'] !== false)
{
	if(strlen($arLoadResult['MESSAGE']) > 0)
		echo $arLoadResult['MESSAGE'];

	echo '<script>Run('.$arLoadResult['STEP'].')</script>';
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>