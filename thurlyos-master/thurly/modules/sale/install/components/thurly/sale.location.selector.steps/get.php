<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
//define("NOT_CHECK_PERMISSIONS", true);

use Thurly\Main;
use Thurly\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"].'/thurly/modules/main/include/prolog_before.php');

Loader::includeModule('sale');

require_once(dirname(__FILE__).'/class.php');

$result = true;
$errors = array();
$data = array();

try
{
	CUtil::JSPostUnescape();

	$request = Main\Context::getCurrent()->getRequest()->getPostList();
	if($request['version'] == '2')
		$data = CThurlyLocationSelectorStepsComponent::processSearchRequestV2($_REQUEST);
	else
		$data = CThurlyLocationSelectorStepsComponent::processSearchRequest();
}
catch(Main\SystemException $e)
{
	$result = false;
	$errors[] = $e->getMessage();
}

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
print(CUtil::PhpToJSObject(array(
	'result' => $result,
	'errors' => $errors,
	'data' => $data
), false, false, true));