<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetAdditionalCSS('/thurly/gadgets/thurly/thurlyos/styles.css');

$logoFile = "/thurly/gadgets/thurly/thurlyos/images/logo-".LANGUAGE_ID.".png";
if (!file_exists($_SERVER["DOCUMENT_ROOT"].$logoFile))
	$logoFile = "/thurly/gadgets/thurly/thurlyos/images/logo-en.png";
?>
<div class="bx-gadget-top-label-wrap"><div class="bx-gadget-top-label"><?echo GetMessage("GD_THURLY24")?></div></div>
<div class="bx-gadget-title-wrap">
	<span class="bx-gadget-title-text"><?echo GetMessage("GD_THURLY24_TITLE")?></span><img src="<?echo $logoFile?>" alt="ThurlyOS"/>
</div>
<a class="bx-gadget-thurlyos-btn" href="<?echo htmlspecialcharsBx(GetMessage("GD_THURLY24_LINK"));?>"><?echo GetMessage("GD_THURLY24_BUTTON")?></a>
<div class="bx-gadget-thurlyos-text-wrap">
	<span class="bx-gadget-thurlyos-text-left"></span><span class="bx-gadget-thurlyos-text">
	<?echo GetMessage("GD_THURLY24_LIST")?>
	<span class="bx-gadget-thurlyos-text-other"><?echo GetMessage("GD_THURLY24_MORE")?></span> <br>
	</span>
</div>
<div class="bx-gadget-shield"></div>
