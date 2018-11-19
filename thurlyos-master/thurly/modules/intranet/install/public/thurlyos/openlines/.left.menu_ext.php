<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/.superleft.menu_ext.php");

if (CModule::IncludeModule("imopenlines"))
{
	if (\Thurly\ImOpenlines\Security\Helper::isLinesMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_LIST_LINES"),
			"/openlines/list/",
			array(),
			array("menu_item_id" => "menu_openlines_lines"),
			""
		);
	}
	//TODO: Temporarily disabled
	/*if (\Thurly\ImOpenlines\Security\Helper::isStatisticsMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_STATISTICS"),
			"/openlines/",
			array(),
			array("menu_item_id" => "menu_openlines_statistics"),
			""
		);
	}*/
	if (CModule::IncludeModule("imconnector"))
	{
		$listActiveConnector = \Thurly\ImConnector\Connector::getListConnectorMenu(true);
		foreach ($listActiveConnector as $idConnector => $fullName)
		{
			$aMenuLinks[] = array(
				empty($listActiveConnector[$idConnector]['short_name'])? $listActiveConnector[$idConnector]['name']:$listActiveConnector[$idConnector]['short_name'],
				"/openlines/connector/?ID=" . $idConnector,
				array(),
				array(
					"title" => $listActiveConnector[$idConnector]['name'],
					"menu_item_id" => "menu_openlines_connector_" . str_replace('.', '_', $idConnector)),
				""
			);
		}
	}

	/**	List */

	if (\Thurly\ImOpenlines\Security\Helper::isCrmWidgetEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_BUTTON"),
			"/openlines/button.php",
			array(),
			array("menu_item_id" => "menu_openlines_button"),
			""
		);
	}

	if (\Thurly\ImOpenlines\Security\Helper::isStatisticsMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_DETAILED_STATISTICS"),
			"/openlines/statistics.php",
			array(),
			array("menu_item_id" => "menu_openlines_detail_statistics"),
			""
		);
	}

	if (\Thurly\ImOpenlines\Security\Helper::isSettingsMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_PERMISSIONS"),
			"/openlines/permissions.php",
			array("/openlines/editrole.php"),
			array("menu_item_id" => "menu_openlines_permissions"),
			""
		);
	}
}