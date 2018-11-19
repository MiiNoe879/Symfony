<?php
namespace Thurly\Crm\Restriction;
class ThurlyOSAccessRestriction extends AccessRestriction
{
	/** @var ThurlyOSRestrictionInfo|null */
	private $restrictionInfo = null;
	public function __construct($name = '', $permitted = false, array $htmlInfo = null, array $popupInfo = null)
	{
		parent::__construct($name, $permitted);
		$this->restrictionInfo = new ThurlyOSRestrictionInfo($htmlInfo, $popupInfo);
	}
	/**
	* @return string
	*/
	public function preparePopupScript()
	{
		return $this->restrictionInfo->preparePopupScript();
	}
	/**
	* @return string
	*/
	public function getHtml()
	{
		return $this->restrictionInfo->getHtml();
	}
}