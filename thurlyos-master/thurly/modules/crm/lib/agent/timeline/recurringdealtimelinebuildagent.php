<?php
namespace Thurly\Crm\Agent\Timeline;

use Thurly\Main;
use Thurly\Crm\Timeline\DealController;

class RecurringDealTimelineBuildAgent extends EntityTimelineBuildAgent
{
	const ITERATION_LIMIT = 500;
	/** @var RecurringDealTimelineBuildAgent|null */
	private static $instance = null;
	/**
	 * @return RecurringDealTimelineBuildAgent|null
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new RecurringDealTimelineBuildAgent();
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
		$itemIDs = $this->filterEntityIDs($itemIDs, \CCrmOwnerType::Deal);
		if(empty($itemIDs))
		{
			return;
		}

		$options = array('EXISTS_CHECK' => false);
		foreach($itemIDs as $itemID)
		{
			DealController::getInstance()->register($itemID, $options);
		}
	}
	protected function getOptionName()
	{
		return '~CRM_BUILD_RECURRING_DEAL_TIMELINE';
	}
	protected function getProgressOptionName()
	{
		return '~CRM_BUILD_RECURRING_DEAL_TIMELINE_PROGRESS';
	}
	protected function getTotalEntityCount()
	{
		return \CCrmDeal::GetListEx(
			array(),
			array('CHECK_PERMISSIONS' => 'N', 'IS_RECURRING' => 'Y'),
			array(),
			false
		);
	}
	protected function getEnityIDs($offsetID, $limit)
	{
		$filter = array('CHECK_PERMISSIONS' => 'N', 'IS_RECURRING' => 'Y');
		if($offsetID > 0)
		{
			$filter['>ID'] = $offsetID;
		}

		$dbResult = \CCrmDeal::GetListEx(
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