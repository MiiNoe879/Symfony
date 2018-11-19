<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_js.php");
/** @global CUser $USER */
global $USER;
/** @global CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

if(!$USER->CanDoOperation('cache_control') || !check_thurly_sessid())
	die(GetMessage("ACCESS_DENIED"));

if($_GET["site_id"] == '')
	die("Empty site_id.");

$sites = CSite::GetByID($_GET["site_id"]);
if(!($site = $sites->Fetch()))
	die("Incorrect site_id.");

$aComponents = explode(",", $_GET["component_name"]);
foreach($aComponents as $component_name)
{
	CThurlyComponent::clearComponentCache($component_name, $site["ID"]);
}
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin_js.php");
?>