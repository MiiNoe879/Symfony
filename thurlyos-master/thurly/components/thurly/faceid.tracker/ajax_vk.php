<?php

define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

\Thurly\Main\Loader::includeModule('faceid');

if (!\Thurly\Faceid\AgreementTable::checkUser($USER->getId()))
{
	die;
}

if (substr($_POST['image'], 0, 5) == 'data:')
{
	$imageContent = str_replace('data:image/jpeg', 'data://image/jpeg', $_POST['image']);
	$fileContent = base64_decode(str_replace('data://image/jpeg;base64,', '', $imageContent));
}
else
{
	$http = new \Thurly\Main\Web\HttpClient;

	if (substr($_POST['image'], 0, 4) != 'http')
	{
		$httpRequest = \Thurly\Main\Context::getCurrent()->getRequest();
		$_POST['image'] = 'http'.($httpRequest->isHttps()?'s':'').'://'.$httpRequest->getHttpHost().$_POST['image'];
	}

	$fileContent = $http->get($_POST['image']);

	if (empty($fileContent))
	{
		echo '{}';
		die;
	}
}

if (!empty($_POST['action']))
{
	if ($_POST['action'] == 'identify' && !empty($_POST['visitor_id']))
	{
		$response = \Thurly\FaceId\FaceId::identifyVk($fileContent);
		$result = $response['result'];
		$items = array();

		$errorResponse = array();

		// get actual balance
		$currentBalance = \Thurly\Main\Config\Option::get('faceid', 'balance', '1000');

		if (isset($response['status']['balance']))
		{
			$currentBalance = (int) $response['status']['balance'];
		}

		if (!empty($response['success']) && !empty($result['found']))
		{
			foreach ($result['items'] as &$vk)
			{
				$personal = array();
				if (substr_count($vk['bdate'], '.') > 1)
					$personal[] = $vk['bdate'];
				if (!empty($vk['city']))
					$personal[] = $vk['city'];
				$vk['personal'] = join(', ', $personal);
			}

			$items = $result['items'];
		}
		elseif (!empty($response['success']['result']['code']) && $response['success']['result']['code'] != \Thurly\FaceId\FaceId::CODE_OK)
		{
			$errorResponse = array(
				'code' => $response['success']['result'],
				'msg' => \Thurly\FaceId\FaceId::getErrorMessage($response['success']['code'])
			);
		}

		echo \Thurly\Main\Web\Json::encode(array(
			'items' => $items,
			'status' => array('balance' => $currentBalance),
			'error' => $errorResponse
		));
	}
	elseif ($_POST['action'] == 'save' && !empty($_POST['visitor_id']))
	{
		$r = \Thurly\Faceid\TrackingVisitorsTable::update($_POST['visitor_id'], array(
			'VK_ID' => $_POST['vk_id']
		));

		echo '{}';
	}
}

CMain::FinalActions();
