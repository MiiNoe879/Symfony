<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.product_row.edit',
	'',
	array(
		'SERVICE_URL_TEMPLATE' => '/mobile/ajax.php?mobile_action=crm_product_row_edit&site_id=#SITE#&#SID#',
		'UID' => 'mobile_crm_product_row_edit'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
