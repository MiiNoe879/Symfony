<?php
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/configs/userconsent.php");
$APPLICATION->SetTitle(GetMessage("USER_CONSENT_TITLE"));

$APPLICATION->IncludeComponent("thurly:intranet.userconsent", "", array(

));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");