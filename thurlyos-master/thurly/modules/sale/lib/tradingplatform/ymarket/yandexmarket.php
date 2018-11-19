<?
namespace Thurly\Sale\TradingPlatform\YMarket;

use Thurly\Sale\TradingPlatform\Platform;

class YandexMarket extends Platform
{
	const TRADING_PLATFORM_CODE = "ymarket";

	public static function getInstance()
	{
		return parent::getInstanceByCode(self::TRADING_PLATFORM_CODE);
	}
}