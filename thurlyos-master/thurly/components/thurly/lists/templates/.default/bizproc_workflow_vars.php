<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
{
	$moduleId = "lists";
	$entity = "BizprocDocument";
}
else
{
	$moduleId = "lists";
	$entity = 'Thurly\Lists\BizprocDocumentLists';
}
$link = str_replace(
	array("#list_id#"),
	array($arResult["VARIABLES"]["list_id"]),
	$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
);
CJSCore::Init(array('lists'));
$isThurlyOSTemplate = (SITE_TEMPLATE_ID == "thurlyos");
if($isThurlyOSTemplate)
{
	$this->SetViewTarget("pagetitle", 100);
}
?>
	<div class="pagetitle-container pagetitle-align-right-container">
		<a href="<?=$link?>" class="lists-list-back">
			<?=GetMessage("CT_BL_LIST_PROCESSES")?>
		</a>
	</div>
<?
if($isThurlyOSTemplate)
{
	$this->EndViewTarget();
}
$APPLICATION->IncludeComponent("thurly:bizproc.workflow.setvar", ".default", array(
	"MODULE_ID" => $moduleId,
	"ENTITY" => $entity,
	"DOCUMENT_TYPE" => "iblock_".$arResult["VARIABLES"]["list_id"],
	"ID" => $arResult['VARIABLES']['ID'],
	"EDIT_PAGE_TEMPLATE" => str_replace(
		array("#list_id#"),
		array($arResult["VARIABLES"]["list_id"]),
		$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_vars"]
	),
	"LIST_PAGE_URL" => str_replace(
		array("#list_id#"),
		array($arResult["VARIABLES"]["list_id"]),
		$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"]
	),
	"SHOW_TOOLBAR" => "N",
	"SET_TITLE" => "Y",
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>