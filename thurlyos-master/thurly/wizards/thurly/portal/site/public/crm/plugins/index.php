<?php
require($_SERVER['DOCUMENT_ROOT'].'/thurly/header.php');
?>

<?$APPLICATION->IncludeComponent(
	'thurly:crm.control_panel',
	'',
	array(
		'ID' => 'CMSPLUGINS',
		'ACTIVE_ITEM_ID' => 'CMSPLUGINS',
		'ENABLE_SEARCH' => false,
	)
);?>

<?$APPLICATION->IncludeComponent(
	'thurly:crm.config.external_plugins',
	'',
	Array(
		'CMS_ID' => $_REQUEST['cms'],
	)
);?>

<?php
require($_SERVER['DOCUMENT_ROOT'].'/thurly/footer.php');