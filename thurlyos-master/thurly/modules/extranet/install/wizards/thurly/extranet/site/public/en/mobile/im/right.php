<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
define('MOBILE_TEMPLATE_CSS', "/im_styles.css");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

CMobile::getInstance()->setLargeScreenSupport(false);
CMobile::getInstance()->setScreenCategory("NORMAL");

\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("api_version", CMobile::getApiVersion());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("platform", CMobile::getPlatform());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("im-resent", 'v3');
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("version", "v5");
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("user", $USER->GetId());

$APPLICATION->IncludeComponent("thurly:mobile.im.recent", ".default", array('POSITION' => 'RIGHT'), false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>