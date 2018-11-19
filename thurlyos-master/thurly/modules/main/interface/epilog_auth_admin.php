<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (isset($_REQUEST['bxsender']))
	return;

include($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/interface/lang_files.php");
?>

	</div><?//login-main-wrapper?>

	<div style="display: none;" id="window_wrapper"></div>

<script type="text/javascript">
BX.ready(BX.defer(function(){
	BX.addClass(document.body, 'login-animate');
	BX.addClass(document.body, 'login-animate-popup');
<?
$arPreload = array(
	'CSS' => array('/thurly/panel/main/admin.css', '/thurly/panel/main/admin-public.css', '/thurly/panel/main/adminstyles_fixed.css', '/thurly/themes/.default/modules.css'),
	'JS' => array('/thurly/js/main/utils.js', '/thurly/js/main/admin_tools.js', '/thurly/js/main/popup_menu.js', '/thurly/js/main/admin_search.js', '/thurly/js/main/dd.js', '/thurly/js/main/core/core_popup.js','/thurly/js/main/core/core_date.js', '/thurly/js/main/core/core_admin_interface.js', '/thurly/js/main/core/core_autosave.js', '/thurly/js/main/core/core_fx.js'),
);
foreach ($arPreload['CSS'] as $key=>$file)
	$arPreload['CSS'][$key] = CUtil::GetAdditionalFileURL($file,true);
foreach ($arPreload['JS'] as $key=>$file)
	$arPreload['JS'][$key] = CUtil::GetAdditionalFileURL($file,true);
?>

	//preload admin scripts&styles
	setTimeout(function() {
		BX.load(['<?=implode("','",$arPreload['CSS'])?>']);
		BX.load(['<?=implode("','",$arPreload['JS'])?>']);
	}, 2000);
}));

new BX.COpener({DIV: 'login_lang_button', ACTIVE_CLASS: 'login-language-btn-active', MENU: <?=CUtil::PhpToJsObject($arLangButton['MENU'])?>});
</script>
</body>
</html>
