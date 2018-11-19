<?

require_once($_SERVER["DOCUMENT_ROOT"] . "/thurly/modules/main/include/prolog_before.php");

\Thurly\Main\Loader::includeModule("mobile");

$componentName = $_GET["componentName"];

if ($USER->IsAuthorized())
{
	if ($componentName)
	{
		$APPLICATION->IncludeComponent("thurly:mobile.jscomponent", "", array(
			"componentName" => $componentName,
		), null, array("HIDE_ICONS" => "Y"));
	}
}
else
{
	Thurly\Mobile\Auth::setNotAuthorizedHeaders();
	echo \Thurly\Main\Web\Json::encode(Thurly\Mobile\Auth::getNotAuthorizedResponse());
}

