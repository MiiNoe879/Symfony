<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

$APPLICATION->IncludeComponent(
	'thurly:crm.config.locations.import',
	'',
	array(
		'AJAX_MODE' => 'Y'
	),
	false
);

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
?>
