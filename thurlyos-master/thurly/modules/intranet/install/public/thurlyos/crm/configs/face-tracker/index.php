<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

/** @global CMain $APPLICATION */
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/face-tracker/index.php");
$APPLICATION->SetTitle(\Thurly\Main\Localization\Loc::getMessage('FACEID_PUBLIC_PAGE_TITLE'));

$APPLICATION->includeComponent('thurly:crm.control_panel', '',
	array(
		'ID' => 'CONFIG',
		'ACTIVE_ITEM_ID' => ''
	)
);
$APPLICATION->IncludeComponent(
		"thurly:faceid.tracker.settings",
		".default"
);


require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");