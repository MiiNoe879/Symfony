<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

?>
	<div style="background: #eef2f4; width: 600px; padding: 5px 0px 0px 20px;">
		<?
		$APPLICATION->IncludeComponent('thurly:bizproc.workflow.setconstants', '',
			array('ID' => $arResult['VARIABLES']['ID'], 'POPUP' => 'N'),
			$component,
			array("HIDE_ICONS" => "Y")
		);
		?>
	</div>
