<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.comm.selector',
	'',
	array(
		'UID' => 'mobile_crm_comm_selector'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
