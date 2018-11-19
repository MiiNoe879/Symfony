<?
use \Thurly\Main\Application;
use \Thurly\Sale\PaySystem;

define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

if (CModule::IncludeModule("sale"))
{
	$context = Application::getInstance()->getContext();
	$request = $context->getRequest();

	$item = PaySystem\Manager::searchByRequest($request);
	if ($item !== false)
	{
		$service = new PaySystem\Service($item);
		if ($service instanceof PaySystem\Service)
			$result = $service->processRequest($request);
	}
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>