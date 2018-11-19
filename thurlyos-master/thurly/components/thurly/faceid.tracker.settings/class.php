<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die;

use \Thurly\Main\Localization\Loc;

class FaceidTrackerSettingsComponent extends CThurlyComponent
{
	protected function checkModules()
	{
		if (!\Thurly\Main\Loader::includeModule('faceid'))
		{
			\ShowError('Module "faceid" is not installed');
			return false;
		}

		if (!\Thurly\Main\Loader::includeModule('crm'))
		{
			\ShowError('Module "crm" is not installed');
			return false;
		}

		return true;
	}

	protected function getCrmSources()
	{
		$sources = \Thurly\Main\Loader::includeModule('crm') ? CCrmStatus::GetStatusList('SOURCE') : array();
		$sources = array("" => \Thurly\Main\Localization\Loc::getMessage("FACEID_FTS_NO_LEAD_SOURCE")) + $sources;

		return $sources;
	}


	protected function showSettings()
	{
		$this->arResult['FTRACKER_AUTO_CREATE_LEAD'] =
			\Thurly\Main\Config\Option::get('faceid', 'ftracker_auto_create_lead', 'N');

		$this->arResult['FTRACKER_LEAD_SOURCE'] =
			\Thurly\Main\Config\Option::get('faceid', 'ftracker_lead_source', '');

		$this->arResult['FTRACKER_SOCNET_ENABLED'] =
			\Thurly\Main\Config\Option::get('faceid', 'ftracker_socnet_enabled', 'Y');

		$this->arResult['CRM_SOURCES'] = $this->getCrmSources();

		$this->arResult['CAN_EDIT'] = true;

		$this->arResult['PATH_TO_FTRACKER'] = '/crm/face-tracker/';

		// output
		$this->includeComponentTemplate();
	}

	protected function updateSettings()
	{
		if (!\check_thurly_sessid())
			return false;

		/** @var \CCrmPerms $crmPerms */
		if(!\CCrmAuthorizationHelper::CheckConfigurationUpdatePermission())
		{
			$this->arResult["ERROR"] = Loc::getMessage("FACEID_FTS_ERROR_PERM");
			return false;
		}

		$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
		$post = $request->getPostList()->toArray();

		$optionValues = array();

		// lead auto create
		$optionValues['FTRACKER_AUTO_CREATE_LEAD'] = $post['FTRACKER_AUTO_CREATE_LEAD'] == 'Y' ? 'Y' : 'N';

		// lead source
		if (in_array($post['FTRACKER_LEAD_SOURCE'], array_keys($this->getCrmSources()), true))
		{
			$optionValues['FTRACKER_LEAD_SOURCE'] = $post['FTRACKER_LEAD_SOURCE'];
		}

		// socnet availability
		$optionValues['FTRACKER_SOCNET_ENABLED'] = !empty($post['FTRACKER_SOCNET_ENABLED']) ? 'Y' : 'N';

		// save new values
		foreach ($optionValues as $option => $value)
		{
			\Thurly\Main\Config\Option::set('faceid', strtolower($option), $value);
		}
	}

	protected function deleteAll()
	{
		if (!\check_thurly_sessid())
			return false;

		foreach (\Thurly\Faceid\TrackingVisitorsTable::getList() as $visitor)
		{
			\Thurly\Faceid\TrackingVisitorsTable::delete($visitor['ID']);
		}

		foreach (\Thurly\Faceid\TrackingVisitsTable::getList() as $visit)
		{
			\Thurly\Faceid\TrackingVisitsTable::delete($visit['ID']);
		}

		$this->arResult['INFO'] = Loc::getMessage("FACEID_FTS_INFO_DELETED");
	}

	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			return false;
		}

		$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
		if ($request->isPost())
		{
			if ($request->getPost('form') == 'ftracker_settings_edit_form')
			{
				$this->updateSettings();
				LocalRedirect(\Thurly\Main\Context::getCurrent()->getServer()->getRequestUri());
			}
			elseif ($request->getPost('form') == 'ftracker_delete_all_form')
			{
				$this->deleteAll();
			}
		}

		return $this->showSettings();
	}
}