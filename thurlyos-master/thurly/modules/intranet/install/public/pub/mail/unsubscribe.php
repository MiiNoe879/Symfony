<?
define('SKIP_TEMPLATE_AUTH_ERROR', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent('thurly:main.mail.unsubscribe', '', array('PAGE' => 'Y', 'ABUSE' => 'Y'));

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");