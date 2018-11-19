<?php

use Thurly\Main\Localization\Loc;

require $_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/intranet/public/crm/configs/emailtracker/index.php');

$APPLICATION->setTitle(Loc::getMessage('CRM_TITLE'));

$APPLICATION->includeComponent(
	'thurly:crm.config.emailtracker',
	'',
	array(),
	false
);

require $_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php';
