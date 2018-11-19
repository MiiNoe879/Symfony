<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

$APPLICATION->IncludeComponent('thurly:tasks.timeline', '', array(
	'IS_AJAX' => 'Y'
));

\CMain::finalActions();
die();