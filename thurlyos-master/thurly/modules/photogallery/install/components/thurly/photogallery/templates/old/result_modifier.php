<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/thurly/js/main/utils.js"></script>', true);

?><script type="text/javascript">
if (typeof(phpVars) != "object")
	phpVars = {};
if (!phpVars.cookiePrefix)
	phpVars.cookiePrefix = '<?=CUtil::JSEscape(COption::GetOptionString("main", "cookie_name", "THURLY_SM"))?>';
if (!phpVars.titlePrefix)
	phpVars.titlePrefix = '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - ';
if (!phpVars.messLoading)
	phpVars.messLoading = '<?=CUtil::JSEscape(getMessage("P_LOADING"))?>';

photoVars = {'templatePath' : '/thurly/components/thurly/photogallery/templates/old/'};

</script>