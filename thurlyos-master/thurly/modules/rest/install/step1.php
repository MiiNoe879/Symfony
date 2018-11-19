<?
if($ex = $APPLICATION->GetException()):
	echo CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
));
else:
?>
<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1" method="post">
	<?=thurly_sessid_post()?>
	<input type="hidden" name="lang" value="<?echo LANG?>" />
	<input type="hidden" name="id" value="rest" />
	<input type="hidden" name="install" value="Y" />
	<input type="hidden" name="step" value="2" />
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_INSTALL")?>" />
</form>
<?
endif;