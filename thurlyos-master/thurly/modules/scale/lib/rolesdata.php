<?php
namespace Thurly\Scale;

/**
 * Class RolesData
 * @package Thurly\Scale
 */
class RolesData
{
	/**
	 * Returns role defenition
	 * @param string $roleId
	 * @return array role params
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function getRole($roleId)
	{
		if(strlen($roleId) <= 0)
			throw new \Thurly\Main\ArgumentNullException("roleId");

		$rolesDefinitions = self::getList();
		$result = array();

		if(isset($rolesDefinitions[$roleId]))
			$result = $rolesDefinitions[$roleId];

		return $result;
	}

	/**
	 * @return array All roles defenitions
	 * @throws \Thurly\Main\IO\FileNotFoundException
	 */
	public static function getList()
	{
		static $def = null;

		if($def == null)
		{
			$filename = \Thurly\Main\Application::getDocumentRoot()."/thurly/modules/scale/include/rolesdefinitions.php";
			$file = new \Thurly\Main\IO\File($filename);

			if($file->isExists())
				require_once($filename);
			else
				throw new \Thurly\Main\IO\FileNotFoundException($filename);

			if(isset($rolesDefinitions))
				$def = $rolesDefinitions;
			else
				$def = array();
		}

		return $def;
	}

	/**
	 * @param string $roleId
	 * @return array graphs
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function getGraphsCategories($roleId)
	{
		if(strlen($roleId) <= 0)
			throw new \Thurly\Main\ArgumentNullException("roleId");

		$result = array();
		$role = static::getRole($roleId);

		if(isset($role["GRAPH_CATEGORIES"]))
			$result = $role["GRAPH_CATEGORIES"];

		return $result;
	}
}