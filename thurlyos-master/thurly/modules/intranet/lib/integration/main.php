<?

namespace Thurly\Intranet\Integration;

final class Main
{
	public static function onAfterIblockSectionUpdate($fields)
	{
		$iblockStructureId = \Thurly\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if (
			$iblockStructureId <= 0 
			|| intval($fields['IBLOCK_ID']) != $iblockStructureId)
		{
			return true;
		}
		
		$iblockSectionId = intval($fields['ID']);
		
		$agents = \CAgent::GetList(array("ID"=>"DESC"), array("NAME" => "\Thurly\Intranet\Integration\Main::reindexUserAgent(".$iblockSectionId."%"));
		while ($agent = $agents->Fetch())
		{
			\CAgent::Delete($agent['ID']);
		}
		
		\CAgent::AddAgent('\Thurly\Intranet\Integration\Main::reindexUserAgent('.$iblockSectionId.');', "intranet", "Y", 10);
		
		return true;
	}
	
	public static function reindexUserAgent($iblockSectionId, $lastUserId = 0)
	{
		$iblockSectionId = intval($iblockSectionId);
		if (!$iblockSectionId)
			return '';

		$lastUserId = intval($lastUserId);
		
		$cursor = \Thurly\Main\UserTable::getList(array(
			'order' => array('ID' => 'ASC'),
			'filter' => array(
				'>ID' => $lastUserId,
				'=UF_DEPARTMENT' => $iblockSectionId,
				'=IS_REAL_USER' => 'Y',
			),
			'select' => array('ID'),
			'offset' => 0,
			'limit' => 100
		));

		$found = false;
		while ($row = $cursor->fetch())
		{
			\Thurly\Main\UserTable::indexRecord($row['ID']);

			$lastUserId = $row['ID'];
			$found = true;
		}

		if ($found)
		{
			return '\Thurly\Intranet\Integration\Main::reindexUserAgent('.$iblockSectionId.', '.$lastUserId.');';
		}
		else
		{
			return '';
		}
	}
}