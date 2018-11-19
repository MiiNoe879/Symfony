<?php
namespace Thurly\Iblock\Model;

use Thurly\Iblock;

class Section
{
	private static $entityInstance = array();

	final public static function compileEntityByIblock($iblockId)
	{
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			return null;

		if (!isset(self::$entityInstance[$iblockId]))
		{
			$className = 'Section'.$iblockId.'Table';
			$entityName = "\\Thurly\\Iblock\\".$className;
			$referenceName = 'Thurly\Iblock\Section'.$iblockId;
			$entity = '
			namespace Thurly\Iblock;
			class '.$className.' extends \Thurly\Iblock\SectionTable
			{
				public static function getUfId()
				{
					return "IBLOCK_'.$iblockId.'_SECTION";
				}
				
				public static function getMap()
				{
					$fields = parent::getMap();
					$fields["PARENT_SECTION"] = array(
						"data_type" => "'.$referenceName.'",
						"reference" => array("=this.IBLOCK_SECTION_ID" => "ref.ID"),
					);
					return $fields;
				}
			}';
			eval($entity);
			self::$entityInstance[$iblockId] = $entityName;
		}

		return self::$entityInstance[$iblockId];
	}
}