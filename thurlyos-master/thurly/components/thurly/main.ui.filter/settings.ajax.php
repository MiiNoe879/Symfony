<?

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

use Thurly\Main\UI\Filter\Actions;
use Thurly\Main\Web;

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$response = new \Thurly\Main\HttpResponse(\Thurly\Main\Application::getInstance()->getContext());
$response->addHeader("Content-Type", "application/json");

$request = Thurly\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new Web\PostDecodeFilter);

if (!$request->isAjaxRequest())
{
	$response->flush(Web\Json::encode(array(
		"error" => "Request is not XHR"
	)));

	die();
}

if (!$request->isPost())
{
	$response->flush(Web\Json::encode(array(
		"error" => "Request is not POST"
	)));

	die();
}


$options = new \Thurly\Main\UI\Filter\Options($request->get("FILTER_ID"), null, $request["common_presets_id"]);
$error = false;

switch ($request->get("action"))
{
	case Actions::SET_TMP_PRESET :
	{
		$options->setFilterSettings("tmp_filter", $request->getPostList()->toArray());
		break;
	}

	case Actions::PIN_PRESET :
	{
		$options->pinPreset($request->getPost("preset_id"));
		break;
	}

	case Actions::SET_FILTER :
	{
		$options->setFilterSettings($request->getPost("preset_id"), $request->getPostList()->toArray());
		break;
	}

	case Actions::SET_FILTER_ARRAY :
	{
		$options->setFilterSettingsArray($request->getPostList()->toArray());
		break;
	}

	case Actions::RESTORE_FILTER :
	{
		$options->restore($request->getPostList()->toArray());
		break;
	}

	case Actions::REMOVE_FILTER :
	{
		$options->deleteFilter($request->getPost("preset_id"), $request->getPost("is_default"));
		break;
	}

	default :
	{
		$error = true;
		break;
	}
}

if (!$error)
{
	$options->save();

	$response->flush(Web\Json::encode($options->getOptions()));
}
else
{
	$response->flush(Web\Json::encode(array(
		"error" => "Unknown action",
		"action" => $request->get("action")
	)));
}