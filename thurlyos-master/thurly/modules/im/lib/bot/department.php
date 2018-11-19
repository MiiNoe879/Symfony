<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage thurlyos
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Im\Bot;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Department
{
	const XML_ID = "im_bot";

	public static function getId()
	{
		if (!\Thurly\Main\Loader::includeModule('intranet') || !\Thurly\Main\Loader::includeModule('iblock'))
			return 0;

		$departments = \CIntranetUtils::getDeparmentsTree(0, false);
		if (!is_array($departments) || !isset($departments[0][0]))
			return 0;

		$departmentRootId = \Thurly\Main\Config\Option::get('intranet', 'iblock_structure', 0);
		if($departmentRootId <= 0)
			return 0;

		$section = \CIBlockSection::GetList(Array(), Array('ACTIVE' => 'Y', 'IBLOCK_ID' => $departmentRootId, 'XML_ID' => self::XML_ID));
		if ($row = $section->Fetch())
		{
			$sectionId = $row['ID'];
		}
		else
		{
			$section = new \CIBlockSection();
			$sectionId = $section->Add(array(
				'IBLOCK_ID' => $departmentRootId,
				'NAME' => Loc::getMessage('BOT_DEPARTMENT_NAME'),
				'SORT' => 20000,
				'IBLOCK_SECTION_ID' => intval($departments[0][0]),
				'XML_ID' => self::XML_ID
			));
		}
		if (!$sectionId)
		{
			$sectionId = intval($departments[0][0]);
		}

		return $sectionId;
	}
}