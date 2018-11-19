<?
/**
 * @var array $arResult
 * @var array $arParams
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('voximplant_transcript', 'crm_activity_planner'));

\Thurly\Main\Page\Asset::getInstance()->addJs("/thurly/components/thurly/player/videojs/video.js");
\Thurly\Main\Page\Asset::getInstance()->addCss("/thurly/components/thurly/voximplant.main/templates/.default/telephony.css");
\Thurly\Main\Page\Asset::getInstance()->addCss("/thurly/components/thurly/voximplant.statistic.detail/player/skins/audio/audio.css");

ShowError($arResult["ERROR_TEXT"]);

if (!$arResult["ENABLE_EXPORT"])
{
	CThurlyOS::initLicenseInfoPopupJS();
	?>
	<script type="text/javascript">
		function viOpenTrialPopup(dialogId)
		{
			B24.licenseInfoPopup.show(dialogId, "<?=CUtil::JSEscape($arResult["TRIAL_TEXT"]['TITLE'])?>", "<?=CUtil::JSEscape($arResult["TRIAL_TEXT"]['TEXT'])?>");
		}
	</script>
	<?
}

$isThurlyOSTemplate = (SITE_TEMPLATE_ID == "thurlyos");
if($isThurlyOSTemplate)
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", "pagetitle-toolbar-field-view");
	$this->SetViewTarget("inside_pagetitle", 0);
	?><div class="pagetitle-container pagetitle-flexible-space"><?
}

$APPLICATION->IncludeComponent(
	"thurly:main.ui.filter",
	"",
	array(
		"GRID_ID" => $arResult["GRID_ID"],
		"FILTER_ID" => $arResult["FILTER_ID"],
		"FILTER" => $arResult["FILTER"],
		"FILTER_PRESETS" => $arResult["FILTER_PRESETS"],
		"ENABLE_LIVE_SEARCH" => true,
		"ENABLE_LABEL" => true
	),
	$component,
	array()
);

?>
	<div class="pagetitle-container pagetitle-align-right-container">
		<a class="webform-small-button webform-small-button-transparent <?=($arResult['ENABLE_EXPORT'] ? '' : 'btn-lock')?>" href="<?=$arResult['EXPORT_HREF']?>">
			<span class="webform-small-button-left"></span>
			<span class="webform-button-icon"></span>
			<span class="webform-small-button-text"><?=GetMessage("TEL_STAT_EXPORT_TO_EXCEL")?></span>
			<span class="webform-small-button-right"></span>
		</a>
	</div>
<?
if($isThurlyOSTemplate)
{
	?></div><?
	$this->EndViewTarget();

	$isAdmin = CModule::IncludeModule('thurlyos') ? \CThurlyOS::isPortalAdmin($USER->getId()) : $USER->IsAdmin();
	if($isAdmin)
	{
		echo Thurly\Voximplant\Ui\Helper::getStatisticStepper();
	}
}
?><div id="tel-stat-grid-container"><?
	$APPLICATION->IncludeComponent(
		"thurly:main.ui.grid",
		"",
		array(
			"GRID_ID" => $arResult["GRID_ID"],
			"HEADERS" => $arResult["HEADERS"],
			"ROWS" => $arResult["ROWS"],
			"NAV_OBJECT" => $arResult["NAV_OBJECT"],
			"SORT" => $arResult["SORT"],
			"ALLOW_COLUMNS_SORT" => true,
			"ALLOW_SORT" => true,
			"ALLOW_PIN_HEADER" => true,
			"SHOW_PAGINATION" => true,
			"SHOW_PAGESIZE" => true,
			"SHOW_ROW_CHECKBOXES" => false,
			"SHOW_CHECK_ALL_CHECKBOXES" => false,
			"SHOW_SELECTED_COUNTER" => false,
			"PAGE_SIZES" => array(
				array("NAME" => "10", "VALUE" => "10"),
				array("NAME" => "20", "VALUE" => "20"),
				array("NAME" => "50", "VALUE" => "50"),
				array("NAME" => "100", "VALUE" => "100"),
			),
			'SHOW_ACTION_PANEL' => true,
			"TOTAL_ROWS_COUNT" => $arResult["ROWS_COUNT"],
			"AJAX_MODE" => "Y",
			"AJAX_ID" => CAjax::GetComponentID('thurly:voximplant.statistic.detail', '.default', ''),
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_HISTORY" => "N",
		),
		$component, array("HIDE_ICONS" => "Y")
	);
?></div><?
\Thurly\Voximplant\Ui\Helper::renderCustomSelectors($arResult['FILTER_ID'], $arResult['FILTER']);
?>
<script>
BX.ready(function()
{
	var container = BX('<?=$arResult["GRID_ID"];?>');
	var player = new BX.Fileman.Player('vi_records_player', {
		'width': 10,
		'height': 10,
		'onInit': function(player)
		{
			player.vjsPlayer.on('pause', function()
			{
				var buttons = BX.findChildrenByClassName(container, 'vi-player-pause');
				for(var i in buttons)
				{
					BX.removeClass(buttons[i], 'vi-player-pause');
				}
			});
		}
	});
	player.isAudio = true;
	var playerNode = player.createElement();
	playerNode.style.display = 'none';
	BX.insertAfter(playerNode, container);
	player.init();
	BX.bindDelegate(container, 'click', {className: 'vi-player-button'}, function(event)
	{
		var buttons = BX.findChildrenByClassName(container, 'vi-player-pause');
		for(var i in buttons)
		{
			BX.removeClass(buttons[i], 'vi-player-pause');
		}
		var target = event.srcElement || event.target;
		var source = target.getAttribute('data-bx-record');
		if(source)
		{
			source = {src: source, type: 'audio/mp3'};
			var currentSource = player.getSource();
			if(currentSource && currentSource.indexOf(source.src) !== -1 && player.isPlaying())
			{
				player.pause();
			}
			else
			{
				player.setSource(source);
				player.play();
				BX.addClass(target, 'vi-player-pause');
			}
			event.preventDefault();
			return false;
		}
	});
});
</script>
