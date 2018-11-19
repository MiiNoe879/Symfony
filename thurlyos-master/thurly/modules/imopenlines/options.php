<?php
if(!$USER->IsAdmin())
	return;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/imopenlines/options.php');

CModule::IncludeModule('imopenlines');

$errorMessage = '';

$aTabs = array(
	array(
		"DIV" => "edit1", "TAB" => GetMessage("IMOPENLINES_TAB_SETTINGS"), "ICON" => "imopenlines_config", "TITLE" => GetMessage("IMOPENLINES_TAB_TITLE_SETTINGS_2"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if(CModule::includeModule('lists') && CModule::includeModule('iblock'))
{
	$quickAnswersLists = array();
	$iblocks = \CIBlock::getList(array('SORT' => 'ASC', 'NAME' => 'ASC'), array('TYPE' => \Thurly\ImOpenlines\QuickAnswers\ListsDataManager::TYPE, 'ACTIVE' => 'Y'));
	while($iblock = $iblocks->fetch())
	{
		if($iblock['ID'] == COption::GetOptionInt("imopenlines", "quick_iblock_id"))
		{
			$iblock['CHECKED'] = true;
		}
		$quickAnswersLists[$iblock['ID']] = $iblock;
	}
}

if(strlen($_POST['Update'])>0 && check_thurly_sessid())
{
	if (strlen($_POST['PUBLIC_URL']) > 0 && strlen($_POST['PUBLIC_URL']) < 12)
	{
		$errorMessage = GetMessage('IMOPENLINES_ACCOUNT_ERROR_PUBLIC');
	}
	else if(strlen($_POST['Update'])>0)
	{
		if ($_POST['PUBLIC_URL'] != COption::GetOptionString("imopenlines", "portal_url"))
		{
			COption::SetOptionString("imopenlines", "portal_url", $_POST['PUBLIC_URL']);
			\Thurly\ImOpenLines\LiveChatManager::updateCommonFiles(Array("cache" => true));
		}

		COption::SetOptionString("imopenlines", "debug", isset($_POST['DEBUG_MODE']));
		COption::SetOptionString("imopenlines", "quick_iblock_id", $_POST['QUICK_IBLOCK_ID']);

		if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
		{
			LocalRedirect($_REQUEST["back_url_settings"]);
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		}
	}
}
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?echo LANG?>">
<?php echo thurly_sessid_post()?>
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();
if ($errorMessage):?>
<tr>
	<td colspan="2" align="center"><b style="color:red"><?=$errorMessage?></b></td>
</tr>
<?endif;?>
<tr>
	<td width="40%"><?=GetMessage("IMOPENLINES_ACCOUNT_URL")?>:</td>
	<td width="60%"><input type="text" name="PUBLIC_URL" value="<?=htmlspecialcharsbx(\Thurly\ImOpenlines\Common::getServerAddress())?>" /></td>
</tr>
<tr>
	<td width="40%"><?=GetMessage("IMOPENLINES_ACCOUNT_DEBUG")?>:</td>
	<td width="60%"><input type="checkbox" name="DEBUG_MODE" value="Y" <?=(COption::GetOptionInt("imopenlines", "debug")? 'checked':'')?> /></td>
</tr>
<tr>
	<td width="40%"><?=GetMessage("IMOPENLINES_QUICK_IBLOCK_ID")?>:</td>
	<td width="60%">
		<select name="QUICK_IBLOCK_ID">
			<option><?=GetMessage("IMOPENLINES_QUICK_IBLOCK_ID_EMPTY");?></option>
			<?
			if(isset($quickAnswersLists))
			{
				foreach($quickAnswersLists as $id => $iblock)
				{
					?><option value="<?=$id;?>"<?if($iblock['CHECKED']){?> selected="selected"<?}?>><?=$iblock['NAME'];?></option><?
				}
			}?>
		</select>
	</td>
</tr>
<?$tabControl->Buttons();?>
<input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
<input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
<?$tabControl->End();?>
</form>