<?
define('BX_DONT_SKIP_PULL_INIT', true);
require($_SERVER["DOCUMENT_ROOT"] . "/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"] . "/thurly/header.php");
CJSCore::Init(array("ls"));
//viewport rewrite
CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");
$frame = \Thurly\Main\Page\Frame::getInstance();
$frame->setEnable();
$frame->setUseAppCache();

\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("api_version", CMobile::getApiVersion());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("platform", CMobile::getPlatform());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("version", "v5");
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("user", $USER->GetID());
$frame->startDynamicWithID("menu");
$APPLICATION->IncludeComponent("thurly:mobile.menu", "flat", array(), false, Array("HIDE_ICONS" => "Y"));
$frame->finishDynamicWithID("menu", $stub = "", $containerId = null, $useBrowserStorage = true);
$APPLICATION->IncludeComponent("thurly:mobile.rtc", "", array(), false, Array("HIDE_ICONS" => "Y"));
?>
<script type="text/javascript">
	app.enableSliderMenu(true);
	app.getToken();
</script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/thurly/footer.php") ?>
