<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\ImOpenlines;

use Thurly\Main\Config\Option;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

class Preset
{
	protected $errors = array();
	protected static $version = 1;
	protected static $versionOptionName = 'preset_version';

	protected static function getVersion()
	{
		return self::$version;
	}

	protected static function getInstalledVersion()
	{
		return (int) Option::get('imopenlines', self::$versionOptionName, 0);
	}

	public static function updateInstalledVersion($version = null)
	{
		if($version === null)
		{
			$version = self::getVersion();
		}

		Option::set('imopenlines', self::$versionOptionName, $version);
	}

	public static function checkVersion()
	{
		return self::getVersion() > self::getInstalledVersion();
	}

	public function install()
	{
		if(!self::checkVersion())
		{
			return true;
		}

		$result = $this->createLiveChat();
		if($result)
		{
			self::updateInstalledVersion();
		}

		$callback = array(__CLASS__, 'installVersion' . self::getVersion());
		if (is_callable($callback))
		{
			call_user_func_array($callback, array());
		}

		return $result;
	}

	public function uninstall()
	{
	}

	protected function createLiveChat()
	{
		$orm = \Thurly\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array('CNT'),
			'runtime' => array(
				new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			),
			'filter' => Array('>CONFIG_ID' => 10)
		));
		$row = $orm->fetch();
		if ($row['CNT'] > 0)
		{
			return true;
		}

		if (!\Thurly\Main\Loader::includeModule('imconnector'))
		{
			return false;
		}

		$orm = \Thurly\ImOpenLines\Model\ConfigTable::getList(Array(
			'select' => Array('ID'),
			'filter' => Array('>ID' => 10)
		));
		if ($row = $orm->fetch())
		{
			$result = \Thurly\ImConnector\Connector::add($row['ID'], 'livechat');
			return $result->isSuccess();
		}

		$configManager = new \Thurly\ImOpenLines\Config();
		$configId = $configManager->create();
		if ($configId)
		{
			$result = \Thurly\ImConnector\Connector::add($configId, 'livechat');
			if (!$result->isSuccess())
			{
				$configManager->delete($configId);
				return false;
			}
		}

		return true;
	}

	/*
	public static function installVersion2()
	{
	}
	*/
}
