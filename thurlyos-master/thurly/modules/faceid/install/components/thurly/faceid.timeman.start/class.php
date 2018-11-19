<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die;

if (!\Thurly\Main\Loader::includeModule("faceid"))
	return;

class FaceidTimemanStartComponent extends CThurlyComponent
{
	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		// disabled for some portals
		if (!\Thurly\FaceId\FaceId::isAvailable())
		{
			die;
		}

		\Thurly\Main\Loader::includeModule('faceid');

		$this->arResult['IS_B24'] = \Thurly\Main\Loader::includeModule('thurlyos');

		// portal has license to module
		$this->arResult['TIMEMAN_AVAILABLE'] = \Thurly\Main\Loader::includeModule('timeman') ||
			($this->arResult['IS_B24'] && \Thurly\ThurlyOS\Feature::isFeatureEnabled("timeman"))
		;

		// portal has license and module is enabled
		$this->arResult['TIMEMAN_ENABLED'] = \Thurly\Main\Loader::includeModule('timeman') ||
			($this->arResult['IS_B24'] && \Thurly\ThurlyOS\Feature::isFeatureEnabled("timeman")
				&& \Thurly\Main\Loader::includeModule('timeman')
			)
		;

		// init popup
		if ($this->arResult['IS_B24'])
		{
			\CThurlyOS::initLicenseInfoPopupJS();
		}

		// output
		$this->includeComponentTemplate();
	}
}