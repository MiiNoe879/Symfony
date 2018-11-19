<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_after.php');

$params = array(
	"DETAIL_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_detail.php",
	"EDIT_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_edit.php"
);

$APPLICATION->IncludeComponent(
	'thurly:thurlycloud.mobile.monitoring.list',
	'sites_list',
	$params,
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_after.php');