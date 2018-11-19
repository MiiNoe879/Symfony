<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?><div class="crm_wrapper"><?
$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.pay_system.list',
	'',
	array('UID' => 'mobile_crm_pay_system_list_#PERSON_TYPE_ID#')
);
?></div><?
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
