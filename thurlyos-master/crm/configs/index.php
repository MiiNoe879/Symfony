<?
/** @global CMain $APPLICATION */

require($_SERVER['DOCUMENT_ROOT'].'/thurly/header.php');
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/index.php");
$APPLICATION->SetTitle(GetMessage('CRM_TITLE'));

$APPLICATION->includeComponent('thurly:crm.control_panel', '',
	array(
		'ID' => 'CONFIG',
		'ACTIVE_ITEM_ID' => ''
	),
	$component
);

$APPLICATION->includeComponent('thurly:crm.configs', '', array('SHOW_TITLE' => 'N'), $component);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
