<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.event.list',
	'',
	array(
		'UID' => 'mobile_crm_event_list'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
