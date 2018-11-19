<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/openlines/.left.menu_ext.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlyos/public/.superleft.menu_ext.php");

if (CModule::IncludeModule("imopenlines"))
{
	$publicFolderOL = \Thurly\ImOpenLines\Common::getPublicFolder();

	if (\Thurly\ImOpenlines\Security\Helper::isLinesMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_LIST_LINES"),
			$publicFolderOL . "list/",
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
			$publicFolderOL,
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
				$publicFolderOL . "connector/?ID=" . $idConnector,
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
			$publicFolderOL . "button.php",
			array(),
			array("menu_item_id" => "menu_openlines_button"),
			""
		);
	}

	if (\Thurly\ImOpenlines\Security\Helper::isStatisticsMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_DETAILED_STATISTICS"),
			$publicFolderOL . "statistics.php",
			array(),
			array("menu_item_id" => "menu_openlines_detail_statistics"),
			""
		);
	}

	if (\Thurly\ImOpenlines\Security\Helper::isSettingsMenuEnabled())
	{
		$aMenuLinks[] = array(
			GetMessage("MENU_IMOL_PERMISSIONS"),
			$publicFolderOL . "permissions.php",
			array($publicFolderOL . "editrole.php"),
			array("menu_item_id" => "menu_openlines_permissions"),
			""
		);
	}
}