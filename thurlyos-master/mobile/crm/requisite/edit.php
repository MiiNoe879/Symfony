<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.client_requisite.edit',
	'',
	array(
		'UID' => 'mobile_crm_client_requisite_edit'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
