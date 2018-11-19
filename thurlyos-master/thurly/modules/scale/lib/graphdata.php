<?php
namespace Thurly\Scale;

/**
 * Class GraphData
 * @package Thurly\Scale
 */
class GraphData
{
	/**
	 * Returns graphics definition
	 * @param string $graphCategory
	 * @return array
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function getGraphs($graphCategory)
	{
		if(strlen($graphCategory) <= 0)
			throw new \Thurly\Main\ArgumentNullException("graphCategory");

		$graphics = self::getList();
		$result = array();

		if(isset($graphics[$graphCategory]))
			$result = $graphics[$graphCategory];

		return $result;
	}

	/**
	 * @return array All graphics
	 * @throws \Thurly\Main\IO\FileNotFoundException
	 */
	public static function getList()
	{
		static $def = null;

		if($def == null)
		{
			$filename = \Thurly\Main\Application::getDocumentRoot()."/thurly/modules/scale/include/graphdefinitions.php";
			$file = new \Thurly\Main\IO\File($filename);

			if($file->isExists())
				require_once($filename);
			else
				throw new \Thurly\Main\IO\FileNotFoundException($filename);

			if(isset($graphics))
				$def = $graphics;
			else
				$def = array();
		}

		return $def;
	}
}