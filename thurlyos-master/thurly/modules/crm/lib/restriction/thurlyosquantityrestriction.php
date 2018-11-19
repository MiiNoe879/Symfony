<?php
namespace Thurly\Crm\Restriction;
class ThurlyOSQuantityRestriction extends QuantityRestriction
{
	/** @var ThurlyOSRestrictionInfo|null */
	private $restrictionInfo = null;
	public function __construct($name = '', $limit = 0, array $htmlInfo = null, array $popupInfo = null)
	{
		parent::__construct($name, $limit);
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