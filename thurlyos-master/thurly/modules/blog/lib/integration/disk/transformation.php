<?php

namespace Thurly\Blog\Integration\Disk;

use Thurly\Main\Loader;
use Thurly\Disk\AttachedObject;

class Transformation
{
	public static function getStatus($params = array())
	{
		$attachedIdList = (
			is_array($params)
			&& !empty($params['attachedIdList'])
			&& is_array($params['attachedIdList'])
				? $params['attachedIdList']
				: array()
		);

		if (
			empty($params['attachedIdList'])
			|| !Loader::includeModule('disk')
			|| !method_exists('Thurly\Disk\View\Video', 'isNeededLimitRightsOnTransformTime')
		)
		{
			return false;
		}

		foreach($attachedIdList as $attachedId)
		{
			$attach = AttachedObject::getById($attachedId);
			if ($attach->getFile()->getView()->isNeededLimitRightsOnTransformTime())
			{
				return true;
			}
		}

		return false;
	}
}
