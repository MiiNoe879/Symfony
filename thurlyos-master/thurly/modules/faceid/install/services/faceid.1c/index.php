<?

define("NOT_CHECK_FILE_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

use Thurly\Main\Localization\Loc;

$APPLICATION->IncludeComponent(
	"thurly:faceid.1c",
	"",
	Array()
);

CMain::FinalActions();