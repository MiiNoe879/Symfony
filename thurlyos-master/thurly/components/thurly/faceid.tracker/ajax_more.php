<?php

define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

\Thurly\Main\Loader::includeModule('faceid');

if (!\Thurly\Faceid\AgreementTable::checkUser($USER->getId()))
{
	die;
}

if (!empty($_POST['last']))
{
	$lastDate = new \Thurly\Main\Type\DateTime(date('Y-m-d H:i:s', $_POST['last']), 'Y-m-d H:i:s');
	$limit = 20;

	$visitors = \Thurly\Faceid\TrackingVisitorsTable::getList(array(
		'order' => array('LAST_VISIT' => 'DESC'),
		'filter' => array('<LAST_VISIT' => $lastDate),
		'limit' => $limit+1
	))->fetchAll();

	$result = array();
	foreach ($visitors as $visitor)
	{
		$result[] = \Thurly\Faceid\TrackingVisitorsTable::toJson($visitor, 0, true);
	}

	// flag if there is something more
	$hasMore = false;
	if (count($result) > $limit)
	{
		unset($result[$limit]);
		$hasMore = true;
	}

	echo \Thurly\Main\Web\Json::encode(array(
		'items' => $result,
		'more' => (int) $hasMore
	));
}

CMain::FinalActions();