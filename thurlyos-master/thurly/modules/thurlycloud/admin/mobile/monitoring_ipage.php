<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/prolog_admin_mobile_after.php');

$APPLICATION->IncludeComponent(
	'thurly:thurlycloud.mobile.monitoring.list',
	'.default',
	array(
		"DETAIL_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_detail.php",
		"LIST_URL" => "/thurly/admin/mobile/thurlycloud_monitoring_list.php"
		),
	false
);

require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_before.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/epilog_admin_mobile_after.php');