<?php
namespace Thurly\Crm\Agent\Timeline;

use Thurly\Main;
use Thurly\Crm\Timeline\CompanyController;

class CompanyTimelineBuildAgent extends EntityTimelineBuildAgent
{
	const ITERATION_LIMIT = 500;
	/** @var CompanyTimelineBuildAgent|null */
	private static $instance = null;
	/**
	 * @return CompanyTimelineBuildAgent|null
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new CompanyTimelineBuildAgent();
		}
		return self::$instance;
	}
	public static function activate()
	{
		\CAgent::AddAgent(
			__CLASS__.'::run();',
			'crm',
			'Y',
			2,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset(), 'FULL')
		);
	}
	//region EntityTimelineBuildAgent
	public function build(array $itemIDs)
	{
		$itemIDs = $this->filterEntityIDs($itemIDs, \CCrmOwnerType::Company);
		if(empty($itemIDs))
		{
			return;
		}

		$options = array('EXISTS_CHECK' => false);
		foreach($itemIDs as $itemID)
		{
			CompanyController::getInstance()->register($itemID, $options);
		}
	}
	protected function getOptionName()
	{
		return '~CRM_BUILD_COMPANY_TIMELINE';
	}
	protected function getProgressOptionName()
	{
		return '~CRM_BUILD_COMPANY_TIMELINE_PROGRESS';
	}
	protected function getTotalEntityCount()
	{
		return \CCrmCompany::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}
	protected function getEnityIDs($offsetID, $limit)
	{
		$filter = array('CHECK_PERMISSIONS' => 'N');
		if($offsetID > 0)
		{
			$filter['>ID'] = $offsetID;
		}

		$dbResult = \CCrmCompany::GetListEx(
			array('ID' => 'ASC'),
			$filter,
			false,
			array('nTopCount' => $limit),
			array('ID')
		);

		$results = array();

		if(is_object($dbResult))
		{
			while($fields = $dbResult->Fetch())
			{
				$results[] = (int)$fields['ID'];
			}
		}
		return $results;
	}
	protected function getIterationLimit()
	{
		return self::ITERATION_LIMIT;
	}
	//endregion
}