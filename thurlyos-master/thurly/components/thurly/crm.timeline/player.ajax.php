<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');
global $DB, $APPLICATION;

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
if($id === '')
{
	$id = 'timelime_player';
}
$path = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
if($type === '')
{
	$type = "audio/mp3";
}
if($path !== '')
{
	$APPLICATION->IncludeComponent(
		"thurly:player",
		"audio",
		array(
			"ADVANCED_MODE_SETTINGS" => "Y",
			"ALLOW_SWF" => "N",
			"AUTOSTART" => "Y",
			"HEIGHT" => "30",
			"MUTE" => "N",
			"TYPE" => $type,
			"PATH" => $path,
			"PLAYER_ID" => $id,
			"PLAYER_TYPE" => "videojs",
			"PLAYLIST_SIZE" => "180",
			"PRELOAD" => "Y",
			"PREVIEW" => "",
			"REPEAT" => "none",
			"SHOW_CONTROLS" => "Y",
			"SKIN" => "timeline_player.css",
			"SKIN_PATH" => "/thurly/js/crm/",
			"USE_PLAYLIST" => "N",
			"VOLUME" => "100",
			"WIDTH" => "350",
			"COMPONENT_TEMPLATE" => "audio",
			"SIZE_TYPE" => "absolute",
			"START_TIME" => "0",
			"PLAYBACK_RATE" => "1"
			),
			false
	);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');