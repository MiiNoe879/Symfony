<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?><div class="crm_wrapper"><?
$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.status.list',
	'',
	array('UID' => 'mobile_crm_status_list_#TYPE_ID#')
);
?></div><?
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
