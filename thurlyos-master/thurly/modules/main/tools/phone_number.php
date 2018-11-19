<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);

if(isset($_REQUEST['tpl']) && isset($_REQUEST['tpls']))
{
	define('SITE_TEMPLATE_ID', $_REQUEST['tpl']);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

/**
 * Thurly vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

$request = \Thurly\Main\Context::getCurrent()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter());

if(check_thurly_sessid())
{
	$action = $request['ACTION'];

	switch($action)
	{
		case 'getCountries':
			$result = GetCountries();
			break;
		default:
			$result = array(
				'ERROR' => 'Unknown action'
			);
			break;
	}

	Header('Content-Type: application/json');
	echo \Thurly\Main\Web\Json::encode($result);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");