<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/face-tracker/index.php");
$APPLICATION->SetTitle(\Thurly\Main\Localization\Loc::getMessage('FACEID_PUBLIC_PAGE_TITLE'));

$APPLICATION->includeComponent('thurly:crm.control_panel', '',
	array(
		'ID' => 'FACETRACKER',
		'ACTIVE_ITEM_ID' => 'FACETRACKER'
	)
);

?><?$APPLICATION->IncludeComponent(
	"thurly:faceid.tracker",
	"",
	Array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>