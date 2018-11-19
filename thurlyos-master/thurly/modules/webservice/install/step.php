<?if(!check_thurly_sessid()) return;?>
<?
echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<br>
<?echo BeginNote();?>
	<font class="text">
	<a href="/thurly/components/thurly/webservice.statistic/distr/ThurlyStat.gadget"><?= GetMessage("WS_GADGET_LINK") ?></a><br><br>
	<?= GetMessage("WS_GADGET_DESCR") ?>
	</font>
<?echo EndNote();?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>
