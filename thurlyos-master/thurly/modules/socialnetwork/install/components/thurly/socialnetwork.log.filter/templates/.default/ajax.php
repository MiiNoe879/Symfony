<?define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$arJsonData = array();

if (
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_thurly_sessid()
)
{
	if (
		isset($_POST['closePopup'])
		&& $_POST['closePopup'] == 'Y'
	)
	{
		CUserOptions::setOption("socialnetwork", "~log_expertmode_popup_show", "N");
		$arJsonData['SUCCESS'] = 'Y';
	}
}

echo \Thurly\Main\Web\Json::encode($arJsonData);
?>
