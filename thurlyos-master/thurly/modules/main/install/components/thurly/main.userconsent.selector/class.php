<?

use Thurly\Main\Localization\Loc;
use Thurly\Main\ErrorCollection;
use Thurly\Main\UserConsent\Agreement;
use Thurly\Main\UserConsent\Intl;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class MainUserConsentSelectorComponent extends CThurlyComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['ID'] = isset($this->arParams['ID']) ? intval($this->arParams['ID']) : null;
		$this->arParams['INPUT_NAME'] = isset($this->arParams['INPUT_NAME']) ? (string) $this->arParams['INPUT_NAME'] : 'AGREEMENT_ID';

		$this->arParams['PATH_TO_ADD'] = isset($this->arParams['PATH_TO_ADD']) ? $this->arParams['PATH_TO_ADD'] : '';
		$this->arParams['PATH_TO_EDIT'] = isset($this->arParams['PATH_TO_EDIT']) ? $this->arParams['PATH_TO_EDIT'] : '';
		$this->arParams['PATH_TO_CONSENT_LIST'] = isset($this->arParams['PATH_TO_CONSENT_LIST']) ? $this->arParams['PATH_TO_CONSENT_LIST'] : '';
		$this->arParams['ACTION_REQUEST_URL'] = isset($this->arParams['ACTION_REQUEST_URL']) ? $this->arParams['ACTION_REQUEST_URL'] : '';
	}

	protected function prepareResult()
	{
		$this->arResult['LIST'] = array();
		$list = Agreement::getActiveList();
		foreach ($list as $id => $name)
		{
			$this->arResult['LIST'][] = array(
				'ID' => $id,
				'NAME' => $name,
				'SELECTED' => $id == $this->arParams['ID'],
			);
		}

		$intl = new Intl(LANGUAGE_ID);
		$this->arResult['DESCRIPTION'] = $intl->getDataValue('DESCRIPTION');

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		$this->errors = new \Thurly\Main\ErrorCollection();
		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}