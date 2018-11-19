<?php
namespace Thurly\Crm\Restriction;
class ThurlyOSSqlRestriction extends SqlRestriction
{
	/** @var ThurlyOSRestrictionInfo|null */
	private $restrictionInfo = null;
	public function __construct($name = '', $threshold = 0, array $htmlInfo = null, array $popupInfo = null)
	{
		parent::__construct($name, $threshold);
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