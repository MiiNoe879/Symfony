<?
use Thurly\Main;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Location\Admin\Helper;

define("NO_AGENT_CHECK", true);
define("NO_KEEP_STATISTIC", true);

$initialTime = time();
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/prolog.php");

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_REINDEX_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

$APPLICATION->IncludeComponent(
	'thurly:sale.location.reindex',
	'admin',
	array(
		'PATH_TO_REINDEX' => Helper::getReindexUrl(),
		'INITIAL_TIME' => $INITIAL_TIME
	),
	false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");?>
