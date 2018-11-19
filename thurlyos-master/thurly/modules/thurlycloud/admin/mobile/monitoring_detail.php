<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_after.php');

$params = array(
	"LIST_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_list.php",
	"EDIT_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_edit.php"
);

$APPLICATION->IncludeComponent(
	'thurly:thurlycloud.mobile.monitoring.detail',
	'.default',
	$params,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_after.php');