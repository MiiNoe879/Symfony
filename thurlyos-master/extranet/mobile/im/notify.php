<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("api_version", CMobile::getApiVersion());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("platform", CMobile::getPlatform());
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("im-notify", 'v1');
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("version", "v5");
\Thurly\Main\Data\AppCacheManifest::getInstance()->addAdditionalParam("user", $USER->GetId());

$APPLICATION->IncludeComponent("thurly:mobile.im.notify", ".default", array(), false, Array("HIDE_ICONS" => "Y"));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>