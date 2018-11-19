<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage iblock
 */
namespace Thurly\Iblock\InheritedProperty;

class SectionTemplates extends BaseTemplate
{
	/**
	 * @param integer $iblockId Identifier of the iblock of the section.
	 * @param integer $sectionId Identifier of the section.
	 */
	function __construct($iblockId, $sectionId)
	{
		$entity = new SectionValues($iblockId, $sectionId);
		parent::__construct($entity);
	}
}