<?php
namespace Thurly\Sale\Exchange\OneC;

use Thurly\Sale\Exchange;

/**
 * Class ProfileDocument
 * @package Thurly\Sale\Exchange\OneC
 * @deprecated
 * For backward compatibility
 */
class ProfileDocument extends UserProfileDocument
{
    private static $FIELD_INFOS = null;

	/**
	 * @return int
	 */
	public function getOwnerEntityTypeId()
	{
		return Exchange\EntityType::PROFILE;
	}
}