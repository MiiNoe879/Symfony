<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main\Loader;
use Thurly\Voximplant\Limits;

class CVoxImplantComponentConfigRent extends \CThurlyComponent
{
	protected $showTemplate = true;

	protected function init()
	{
		$request = Thurly\Main\Context::getCurrent()->getRequest();

		if (isset($request['AJAX_CALL']) && $request['AJAX_CALL'] == 'Y')
		{
			$this->showTemplate = false;
			return;
		}

		if(isset($this->arParams['TEMPLATE_HIDE']) && $this->arParams['TEMPLATE_HIDE'] === 'Y')
			$this->showTemplate = false;
	}

	protected function prepareData()
	{
		$this->arResult['LIST_RENT_NUMBERS'] = array();
		$res = Thurly\Voximplant\ConfigTable::getList(array(
			'filter' => Array('=PORTAL_MODE' => CVoxImplantConfig::MODE_RENT)
		));
		while ($row = $res->fetch())
		{
			$this->arResult['LIST_RENT_NUMBERS'][$row['ID']] = array(
				'PHONE_NAME' => htmlspecialcharsbx($row['PHONE_NAME']),
				'PHONE_NAME_FORMATTED' => htmlspecialcharsbx(\Thurly\Main\PhoneNumber\Parser::getInstance()->parse($row['PHONE_NAME'])->format()),
				'PHONE_VERIFIED' => $row['PHONE_VERIFIED'] == 'Y',
				'PHONE_COUNTRY_CODE' => $row['PHONE_COUNTRY_CODE'],
				'TO_DELETE' => $row['TO_DELETE'] == 'Y',
			);
		}
		$this->arResult['CAN_RENT_NUMBER'] = Limits::canRentNumber();
	}

	/**
	 * Executes component
	 */
	public function executeComponent()
	{
		if (!Loader::includeModule('voximplant'))
			return false;

		$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
		if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
			return false;

		$this->init();
		$this->prepareData();
		if ($this->showTemplate)
			$this->includeComponentTemplate();

		return $this->arResult;
	}
}


?>