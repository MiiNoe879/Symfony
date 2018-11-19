<?
namespace Thurly\Main\Service\GeoIp;

use Thurly\Main;

/**
 * Class Result
 * @package Thurly\Main\Service\GeoIp
 * Contains info about success or error descriptions of receiving geolocation information,
 * and geolocation information.
 */
class Result extends Main\Result
{
	/** @var Data Geolocation data */
	protected $geoData = null;

	/**
	 * Result constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->geoData = new Data();
	}

	/**
	 * @return Data
	 */
	public function getGeoData()
	{
		return $this->geoData;
	}

	/**
	 * @param Data $geoData
	 */
	public function setGeoData(Data $geoData)
	{
		$this->geoData = $geoData;
	}
}

