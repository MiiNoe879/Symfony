<?
define("BX_DONT_INCLUDE_MOBILE_TEMPLATE_CSS", "Y");
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

?><?$APPLICATION->IncludeComponent(
	'thurly:timeman',
	'mobile',
	array(),
	null
);?><?

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
