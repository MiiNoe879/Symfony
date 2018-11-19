<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage intranet
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Rest\APAuth;

use Thurly\Main\Authentication\ApplicationPasswordTable;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * @deprecated
 *
 * use \Thurly\Rest\APAuth\PasswordTable
 */
class Application extends \Thurly\Main\Authentication\Application
{
	const ID = 'rest';

	protected $validUrls = array(
		"/rest/",
	);

	public static function onApplicationsBuildList()
	{
		return array(
			"ID" => static::ID,
			"NAME" => Loc::getMessage("REST_APP_TITLE"),
			"DESCRIPTION" => Loc::getMessage("REST_APP_DESC"),
			"SORT" => 1000,
			"CLASS" => __CLASS__,
			"VISIBLE" => false,
		);
	}

	/**
	 * Generates AP for REST access.
	 *
	 * @param string $siteTitle Site title for AP description.
	 *
	 * @return bool|string password or false
	 * @throws \Exception
	 */
	public static function generateAppPassword($siteTitle, array $scopeList)
	{
		global $USER;

		$password = ApplicationPasswordTable::generatePassword();

		$res = ApplicationPasswordTable::add(array(
			'USER_ID' => $USER->getID(),
			'APPLICATION_ID' => static::ID,
			'PASSWORD' => $password,
			'DATE_CREATE' => new DateTime(),
			'COMMENT' => Loc::getMessage('REST_APP_COMMENT'),
			'SYSCOMMENT' => Loc::getMessage('REST_APP_SYSCOMMENT', array(
				'#TITLE#' => $siteTitle,
			)),
		));

		if($res->isSuccess())
		{
			$scopeList = array_unique($scopeList);
			foreach($scopeList as $scope)
			{
				PermissionTable::add(array(
					'PASSWORD_ID' => $res->getId(),
					'PERM' => $scope,
				));
			}

			return $password;
		}

		return false;
	}
}
