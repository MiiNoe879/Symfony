<?php
namespace Thurly\Crm\Agent\Activity;
use Thurly\Main\Type\DateTime;
use Thurly\Crm\Agent\AgentBase;
use Thurly\Crm\Pseudoactivity\Entity\WaitTable;
use \Thurly\Crm\Pseudoactivity\WaitEntry;

class WaitAgent extends AgentBase
{
	public static function doRun()
	{
		$now = time() + \CTimeZone::GetOffset();
		//\CCrmUtils::Trace("WaitAgent: run", ConvertTimeStamp($now, 'FULL'), 1);
		$dbResult = WaitTable::getList(
			array(
				'select' => array('ID', 'END_TIME', 'COMPLETED'),
				'filter' => array(
					'COMPLETED' => 'N',
					'<=END_TIME' => DateTime::createFromTimestamp($now)
				),
				'order' => array('ID' => 'ASC')
			)
		);

		$entityIDs = array();
		while($fields = $dbResult->Fetch())
		{
			//\CCrmUtils::Trace("WaitAgent: processing activity", mydump($fields), 1);
			$entityIDs[] = (int)$fields['ID'];
		}

		if(count($entityIDs) > 0)
		{
			foreach($entityIDs as $entityID)
			{
				WaitEntry::complete($entityID, true);
			}
		}
		return true;
	}

	public static function isActive()
	{
		$dbResult = \CAgent::GetList(
			array('ID' => 'DESC'),
			array("NAME" => __CLASS__.'::run(%')
		);
		return is_array($dbResult->Fetch());
	}

	public static function activate()
	{
		\CAgent::AddAgent(
			__CLASS__.'::run();',
			'crm',
			'N',
			3600,
			'',
			'Y',
			ConvertTimeStamp(time() + \CTimeZone::GetOffset(), 'FULL')
		);
	}
}
