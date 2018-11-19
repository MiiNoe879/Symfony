<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!\Thurly\Main\Loader::includeModule("faceid"))
	return;

\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

class FaceIdFaceTrackerComponent extends CThurlyComponent
{
	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		global $USER;

		// disabled for some portals
		if (!\Thurly\FaceId\FaceId::isAvailable())
		{
			die;
		}

		// check agreement
		if (!$USER->getId())
		{
			$this->includeComponentTemplate();
			return;
		}

		if (!empty($_POST['accept']) && !\Thurly\Faceid\AgreementTable::checkUser($USER->getId()))
		{
			$signer = new \Thurly\Main\Security\Sign\Signer;
			$sign = base64_decode($signer->unsign($_POST['sign'], 'bx.faceid.agreement'));
			$ar = explode('_', $sign);
			$userId = end($ar);
			if ((int) $userId === (int) $USER->getId())
			{
				\Thurly\Faceid\AgreementTable::add(array(
					'USER_ID' => $userId,
					'NAME' => $USER->GetFullName(),
					'EMAIL' => $USER->GetEmail(),
					'DATE' => new \Thurly\Main\Type\DateTime,
					'IP_ADDRESS' => \Thurly\Main\Context::getCurrent()->getRequest()->getRemoteAddress()
				));
			}
		}

		$this->arResult['HAS_AGREEMENT'] = \Thurly\Faceid\AgreementTable::checkUser($USER->getId());
		if (!$this->arResult['HAS_AGREEMENT'])
		{
			$signer = new \Thurly\Main\Security\Sign\Signer;
			$this->arResult['AGREEMENT_SIGN'] = $signer->sign(
				base64_encode('FACEID_SIGN_USER_ID_'.$USER->getId()),
				'bx.faceid.agreement'
			);
		}

		// today stats
		$fromDate = \Thurly\Main\Type\DateTime::createFromTimestamp(mktime(-1, 0, 0));
		$fromDateServer = clone $fromDate;

		// adapt to user timezone
		$userTimeOffset = CTimeZone::GetOffset();
		$fromUserTimeInterval = -$userTimeOffset.' seconds';
		$fromDateServer->add($fromUserTimeInterval);

		// count users for today
		$query = new \Thurly\Main\Entity\Query(\Thurly\Faceid\TrackingVisitorsTable::getEntity());
		$query->addFilter('>LAST_VISIT', $fromDateServer);

		$query->addSelect(new \Thurly\Main\Entity\ExpressionField('NEW_VISITORS',
			'SUM(CASE WHEN %s = 1 THEN 1 ELSE 0 END)', 'VISITS_COUNT'
		));

		$query->addSelect(new \Thurly\Main\Entity\ExpressionField('OLD_VISITORS',
			'SUM(CASE WHEN %s > 1 THEN 1 ELSE 0 END)', 'VISITS_COUNT'
		));

		$query->addSelect(new \Thurly\Main\Entity\ExpressionField('CRM_VISITORS',
			'SUM(CASE WHEN %s > 0 THEN 1 ELSE 0 END)', 'CRM_ID'
		));

		$query->addSelect(new \Thurly\Main\Entity\ExpressionField('TOTAL_VISITORS',
			'SUM(1)'
		));

		$this->arResult['STATS'] = $query->exec()->fetch();

		// replace null for 0
		foreach ($this->arResult['STATS'] as $k => $v)
		{
			if ($v === null)
			{
				$this->arResult['STATS'][$k] = 0;
			}
		}

		// last visitors
		$visitors = \Thurly\Faceid\TrackingVisitorsTable::getList(array(
			'order' => array('LAST_VISIT' => 'DESC'),
			'limit' => 20
		))->fetchAll();

		$visitors = array_reverse($visitors);

		// get crm info for these visitors
		$faceIds = array_map(function ($v){ return $v['FACE_ID']; }, $visitors);

		if (!empty($faceIds))
		{
			$crmRecords = \Thurly\Faceid\TrackingVisitorsTable::getCrmInfoByFace($faceIds);
		}

		foreach ($visitors as &$visitor)
		{
			$visitor['CRM'] = array();

			if (!empty($visitor['FACE_ID']) && !empty($crmRecords[$visitor['FACE_ID']]))
			{
				$visitor['CRM'] = $crmRecords[$visitor['FACE_ID']];
			}
		}

		echo $this->getLastVisitorsJson($visitors);

		// balance
		if ($this->arResult['HAS_AGREEMENT'])
		{
			// renew from cloud
			\Thurly\FaceId\FaceId::getBalance();
		}

		$this->arResult['BALANCE'] = \Thurly\Main\Config\Option::get('faceid', 'balance', '1000');

		$this->arResult['BUY_MORE_URL'] = \Thurly\Main\ModuleManager::isModuleInstalled('thurlyos')
			? "/settings/license_face.php"
			: "https://www.1c-thurly.ru/buy/intranet.php#tab-face-link";

		$this->includeComponentTemplate();
	}

	protected function getLastVisitorsJson($visitors)
	{
		$result = array();
		foreach ($visitors as $visitor)
		{
			$result[] = \Thurly\Faceid\TrackingVisitorsTable::toJson($visitor, 0, true);
		}

		$out =  "<script type=\"text/javascript\">
			window.FACEID_LAST_VISITORS = ".\Thurly\Main\Web\Json::encode($result).";			
		</script>";

		return $out;
	}

}