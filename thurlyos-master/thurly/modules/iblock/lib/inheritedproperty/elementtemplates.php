<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage iblock
 */
namespace Thurly\Iblock\InheritedProperty;

use Thurly\Iblock\Template\Entity\Element;

class ElementTemplates extends BaseTemplate
{
	/**
	 * @param integer $iblockId Identifier of the iblock of element.
	 * @param integer $elementId Identifier of the element.
	 */
	function __construct($iblockId, $elementId)
	{
		$entity = new ElementValues($iblockId, $elementId);
		parent::__construct($entity);
	}
}