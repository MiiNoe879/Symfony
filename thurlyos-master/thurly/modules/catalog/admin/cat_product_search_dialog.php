<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('DisableEventsCheck', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
global $APPLICATION;
CUtil::JSPostUnescape();
$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent("thurly:catalog.product.search",'.default');
die();