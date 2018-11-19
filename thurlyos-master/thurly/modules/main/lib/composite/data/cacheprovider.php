<?
namespace Thurly\Main\Composite\Data;

use Thurly\Main;

abstract class CacheProvider
{
	abstract public function isCacheable();
	abstract public function setUserPrivateKey();
	abstract public function getCachePrivateKey();
	abstract public function onBeforeEndBufferContent();
}

class_alias("Thurly\\Main\\Composite\\Data\\CacheProvider", "Thurly\\Main\\Data\\StaticCacheProvider");
