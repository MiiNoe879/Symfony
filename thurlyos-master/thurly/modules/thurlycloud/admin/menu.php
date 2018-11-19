<?php
IncludeModuleLangFile(__FILE__);
/** @global CUser $USER */
$menu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "thurlycloud",
	"sort" => 1645,
	"text" => GetMessage("BCL_MENU_ITEM"),
	"icon" => "thurlycloud_menu_icon",
	"page_icon" => "thurlycloud_page_icon",
	"items_id" => "menu_thurlycloud",
	"items" => array(),
);

if (
	$USER->CanDoOperation("thurlycloud_cdn")
	&& !IsModuleInstalled('intranet')
)
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_CONTROL_ITEM"),
		"url" => "thurlycloud_cdn.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"thurlycloud_cdn.php",
		),
	);
}

if ($USER->CanDoOperation("thurlycloud_backup"))
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_BACKUP_ITEM"),
		"url" => "thurlycloud_backup.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"thurlycloud_backup.php",
		),
	);
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_BACKUP_JOB_ITEM"),
		"url" => "thurlycloud_backup_job.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"thurlycloud_backup_job.php",
		),
	);
}

if ($USER->CanDoOperation("thurlycloud_monitoring"))
{
	$menu["items"][] = array(
		"text" => GetMessage("BCL_MENU_MONITORING_ITEM"),
		"url" => "thurlycloud_monitoring_admin.php?lang=".LANGUAGE_ID,
		"more_url" => array(
			"thurlycloud_monitoring_admin.php",
			"thurlycloud_monitoring_edit.php",
		),
	);
}

if ($menu["items"])
{
	return $menu;
}
else
{
	return false;
}
