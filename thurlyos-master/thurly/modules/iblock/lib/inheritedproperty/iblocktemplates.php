<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage iblock
 */
namespace Thurly\Iblock\InheritedProperty;

class IblockTemplates extends BaseTemplate
{
	/**
	 * @param integer $iblockId Identifier of the iblock.
	 */
	function __construct($iblockId)
	{
		$entity = new IblockValues($iblockId);
		parent::__construct($entity);
	}
}