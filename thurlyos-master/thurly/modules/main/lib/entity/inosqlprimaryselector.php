<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2013 Thurly
 */

namespace Thurly\Main\Entity;

interface INosqlPrimarySelector
{
	public function getEntityByPrimary(\Thurly\Main\Entity\Base $entity, $primary, $select);
}
