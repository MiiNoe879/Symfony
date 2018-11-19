<?php

define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

\Thurly\Main\Loader::includeModule('faceid');
\Thurly\Main\Loader::includeModule('crm');

if (!\Thurly\Faceid\AgreementTable::checkUser($USER->getId()))
{
	die;
}

CUtil::JSPostUnescape();

if (!empty($_POST['action']))
{
	$lead = \Thurly\Faceid\TrackingVisitorsTable::createCrmLead($_POST['visitor_id'], $_POST['lead_title']);
	echo \Thurly\Main\Web\Json::encode(array('id' => $lead['ID'], 'url' => '/crm/lead/show/'.$lead['ID'].'/', 'name' => $lead['TITLE']));
}

CMain::FinalActions();