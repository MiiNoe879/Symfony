<? require($_SERVER["DOCUMENT_ROOT"] . "/thurly/header.php"); ?>
	<script type="text/javascript">
		app.enableSliderMenu(true);
	</script>
<?
$APPLICATION->IncludeComponent(
	'thurly:mobileapp.menu',
	'mobile',
	array("MENU_FILE_PATH"=>"/#folder#/.mobile_menu.php"),
	false,
	Array('HIDE_ICONS' => 'Y'));
?>


<? require($_SERVER["DOCUMENT_ROOT"] . "/thurly/footer.php"); ?>