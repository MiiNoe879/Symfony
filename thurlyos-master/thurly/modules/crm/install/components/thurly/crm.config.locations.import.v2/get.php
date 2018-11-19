<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);

use Thurly\Main;
use Thurly\Main\Loader;

$initialTime = time();

require_once($_SERVER["DOCUMENT_ROOT"].'/thurly/modules/main/include/prolog_before.php');

Loader::includeModule('sale');
Loader::includeModule('crm');

require_once(dirname(__FILE__).'/class.php');

CUtil::JSPostUnescape();

$result = true;
$errors = array();

$result = CThurlyCrmConfigLocationImport2Component::doAjaxStuff(array(
	'INITIAL_TIME' => $initialTime
));

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
print(CUtil::PhpToJSObject(array(
	'result' => empty($result['ERRORS']),
	'errors' => $result['ERRORS'],
	'data' => $result['DATA']
), false, false, true));