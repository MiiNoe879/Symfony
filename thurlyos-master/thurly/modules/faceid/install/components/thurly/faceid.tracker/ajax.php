<?php

define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$VISIT_DURATION = 3600*2;

\Thurly\Main\Loader::includeModule('faceid');

use Thurly\Main\Localization\Loc;

if (!\Thurly\Faceid\AgreementTable::checkUser($USER->getId()))
{
	die;
}

$imageContent = str_replace('data:image/jpeg', 'data://image/jpeg', $_POST['image']);

$fileContent = base64_decode(str_replace('data://image/jpeg;base64,', '', $imageContent));

if (!empty($_POST['action']))
{
	// current balance cache
	$currentBalance = \Thurly\Main\Config\Option::get('faceid', 'balance', '1000');

	if ($_POST['action'] == 'identify')
	{
		$visitor = null;
		$confidence = 0;

		$response = \Thurly\FaceId\FaceId::identify($fileContent, 'ftracker');
		$result = $response['result'];

		$errorResponse = array();

		// get actual balance
		if (isset($response['status']['balance']))
		{
			$currentBalance = (int) $response['status']['balance'];
		}

		if (!empty($response['success']) && !empty($response['result']['found']))
		{
			$faceId = $result['items'][0]['face_id'];
			$confidence = round($result['items'][0]['confidence']);

			$visitor = \Thurly\Faceid\TrackingVisitorsTable::getRow(array(
				'filter' => array('=FACE_ID' => $faceId)
			));

			if (!empty($visitor))
			{
				$visitorId = $visitor['ID'];

				// check if it is new visit
				$currentTime = new \Thurly\Main\Type\DateTime;
				$diff = $currentTime->getTimestamp() - $visitor['LAST_VISIT']->getTimestamp();

				if ($diff > $VISIT_DURATION)
				{
					// register new hit
					\Thurly\Faceid\TrackingVisitsTable::registerVisit($visitorId);
					$visitor = \Thurly\Faceid\TrackingVisitorsTable::getById($visitorId)->fetch();
				}
				else
				{
					// update last visit
					\Thurly\Faceid\TrackingVisitorsTable::update($visitorId, array(
						'LAST_VISIT' => $currentTime
					));

					$visitor['LAST_VISIT'] = $currentTime;
				}
			}
			else
			{
				// photo has been added by another module
				// create visitor
				$face = \Thurly\Faceid\FaceTable::getRowById($faceId);
				if ($face)
				{
					$visitorId = \Thurly\Faceid\TrackingVisitorsTable::add(array(
						'FILE_ID' => $face['FILE_ID'],
						'FACE_ID' => $face['ID'],
						'FIRST_VISIT' => new \Thurly\Main\Type\DateTime
					))->getId();

					if ($visitorId)
					{
						\Thurly\Faceid\TrackingVisitsTable::registerVisit($visitorId);
						$visitor = \Thurly\Faceid\TrackingVisitorsTable::getById($visitorId)->fetch();

						// add to crm if there is no yet
						$crmRecord = \Thurly\Faceid\TrackingVisitorsTable::getCrmInfoByFace($visitor['FACE_ID']);
						if (empty($crmRecord))
						{
							// lead auto creation
							if (\Thurly\Main\Config\Option::get('faceid', 'ftracker_auto_create_lead') == 'Y')
							{
								\Thurly\Faceid\TrackingVisitorsTable::createCrmLead(
									$visitor,
									Loc::getMessage('FACEID_VISITOR')." ".$visitor['ID']
								);
							}
						}
					}
					else
					{
						$errorResponse = array('msg' => 'error while creating visitor');
					}
				}
				else
				{
					$errorResponse = array('msg' => Loc::getMessage("FACEID_TRACKER_CMP_AJAX_NOT_FOUND_LOCAL_DB"));
				}
			}
		}
		elseif (!$result['found'] && $result['code'] == \Thurly\FaceId\FaceId::CODE_OK_UNKNOWN_PERSON)
		{
			$currentTime = new \Thurly\Main\Type\DateTime;

			$response = \Thurly\FaceId\FaceId::add($fileContent, 'ftracker');

			// get actual balance
			if (isset($response['status']['balance']))
			{
				$currentBalance = (int) $response['status']['balance'];
			}

			if (!empty($response['success']) && !empty($response['result']['added']))
			{
				$faceId = $response['result']['item']['face_id'];
				$fileId = $response['result']['item']['file_id'];

				// add visitor
				$visitorId = \Thurly\Faceid\TrackingVisitorsTable::add(array(
					'FILE_ID' => $fileId,
					'FACE_ID' => $faceId,
					'FIRST_VISIT' => $currentTime
				))->getId();

				if ($visitorId)
				{
					\Thurly\Faceid\TrackingVisitsTable::registerVisit($visitorId);
					$visitor = \Thurly\Faceid\TrackingVisitorsTable::getById($visitorId)->fetch();

					// lead auto creation
					if (\Thurly\Main\Config\Option::get('faceid', 'ftracker_auto_create_lead') == 'Y')
					{
						\Thurly\Faceid\TrackingVisitorsTable::createCrmLead(
							$visitor,
							Loc::getMessage('FACEID_VISITOR')." ".$visitor['ID']
						);
					}
				}
				else
				{
					// visitor has not been added
					$errorResponse = array('msg' => 'error while creating visitor');
				}
			}
			else
			{
				$errorResponse = array(
					'code' => $response['result']['code'],
					'msg' => \Thurly\FaceId\FaceId::getErrorMessage($response['result']['code'])
				);
			}
		}

		// visitor info
		$outputVisitor = array();

		if (!empty($visitor))
		{
			$outputVisitor = \Thurly\Faceid\TrackingVisitorsTable::toJson($visitor, $confidence, true);
		}

		// error info
		if (empty($errorResponse) && !empty($result['code']) && $result['code'] !== \Thurly\FaceId\FaceId::CODE_OK)
		{
			$errorResponse = array(
				'code' => $result['code'],
				'msg' => \Thurly\FaceId\FaceId::getErrorMessage($result['code'])
			);
		}

		// output
		echo \Thurly\Main\Web\Json::encode(array(
			'visitor' => $outputVisitor,
			'status' => array(
				'balance' => $currentBalance
			),
			'error' => $errorResponse
		));
	}
}

CMain::FinalActions();
