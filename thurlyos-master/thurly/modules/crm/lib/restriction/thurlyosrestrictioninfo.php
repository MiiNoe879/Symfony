<?php
namespace Thurly\Crm\Restriction;
use Thurly\Main;
use \Thurly\Crm\Integration;

class ThurlyOSRestrictionInfo
{
	/** @var array|null  */
	private $htmlInfo = null;
	/** @var array|null  */
	private $popupInfo = null;
	public function __construct(array $htmlInfo = null, array $popupInfo = null)
	{
		if($htmlInfo !== null)
		{
			$this->htmlInfo = $htmlInfo;
		}

		if($popupInfo !== null)
		{
			$this->popupInfo = $popupInfo;
		}
	}

	/**
	* @return string
	*/
	public function preparePopupScript()
	{
		return $this->popupInfo !== null
			? Integration\ThurlyOSManager::prepareLicenseInfoPopupScript($this->popupInfo)
			: '';
	}
	/**
	* @return string
	*/
	public function getHtml()
	{
		return $this->htmlInfo !== null
			? Integration\ThurlyOSManager::prepareLicenseInfoHtml($this->htmlInfo)
			: '';
	}
}