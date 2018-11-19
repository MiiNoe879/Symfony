<?php

require($_SERVER['DOCUMENT_ROOT'].'/thurly/header.php');

$APPLICATION->includeComponent(
	'thurly:intranet.mail.config',
	'',
	array(
		'SEF_MODE' => 'Y',
		'SEF_FOLDER' => '#SITE_DIR#company/personal/mail/',
	)
);

require($_SERVER['DOCUMENT_ROOT'].'/thurly/footer.php');

