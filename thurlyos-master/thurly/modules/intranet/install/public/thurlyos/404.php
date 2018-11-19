<?
include_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/urlrewrite.php');

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");

require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/404.php");

$APPLICATION->SetTitle(GetMessage("ERROR_404_TITLE"));
?>
<div class="error-404-block-wrap">
	<div class="error-404-block">
		<div class="error-404-text1"><?=GetMessage("ERROR_404_TEXT1")?></div>
		<div class="error-404-text2"><?=GetMessage("ERROR_404_TEXT2")?></div>
		<div class="error-404-footer"></div>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>