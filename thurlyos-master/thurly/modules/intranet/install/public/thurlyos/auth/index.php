<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/auth/index.php");

if (is_string($_REQUEST["backurl"]) && strpos($_REQUEST["backurl"], "/") === 0)
{
	Thurly\Intranet\Composite\CacheProvider::deleteUserCache();
	LocalRedirect($_REQUEST["backurl"]);
}

$APPLICATION->SetTitle(GetMessage("THURLY24_AUTH_TITLE"));
?>
<p><?=GetMessage("THURLY24_AUTH_DESCRIPTION")?></p>

<p><a href="<?=SITE_DIR?>"><?=GetMessage("THURLY24_AUTH_BACK_URL")?></a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>