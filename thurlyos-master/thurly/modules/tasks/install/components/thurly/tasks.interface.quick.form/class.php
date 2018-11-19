<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CThurlyComponent::includeComponentClass("thurly:tasks.base");

class TasksQuickFormComponent extends TasksBaseComponent
{
	protected function checkParameters()
	{
		parent::checkParameters();

		$arParams =& $this->arParams;

		static::tryParseStringParameter($arParams["NAME_TEMPLATE"], \CSite::GetNameFormat(false));
	}

	protected function getData()
	{
		parent::getData();

		$this->arResult["DESTINATION"] = \Thurly\Tasks\Integration\SocialNetwork::getLogDestination('TASKS', array(
			'USE_PROJECTS' => 'Y'
		));
		$this->arResult["GROUP"] = \CSocNetGroup::getByID($this->arParams["GROUP_ID"]);

		$canAddMailUsers = (
			\Thurly\Main\ModuleManager::isModuleInstalled("mail") &&
			\Thurly\Main\ModuleManager::isModuleInstalled("intranet") &&
			(
				!\Thurly\Main\Loader::includeModule("thurlyos")
				|| \CThurlyOS::isEmailConfirmed()
			)
		);

		$this->arResult["CAN"] = array(
			"addMailUsers" => $canAddMailUsers,
			"manageTask" => \Thurly\Tasks\Util\Restriction::canManageTask()
		);


		$user = \CUser::getByID($this->arParams["USER_ID"]);
		$this->arResult["USER"] = $user->fetch();
	}
}