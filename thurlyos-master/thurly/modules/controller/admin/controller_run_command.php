<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
$member_id = intval($_REQUEST['member']);

if (!CModule::IncludeModule("controller"))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$bCanRunCommand = false;
if ($USER->CanDoOperation("controller_run_command"))
{
	$bCanRunCommand = true;
}
else
{
	$grantList = \Thurly\Controller\AuthGrantTable::getActiveForControllerMember($member_id, $USER->GetID(), $USER->GetUserGroupArray());
	while ($grant = $grantList->fetch())
	{
		if ($grant["SCOPE"] === "php")
		{
			$bCanRunCommand = true;
		}
	}
}

if (!$bCanRunCommand)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/controller/prolog.php");

IncludeModuleLangFile(__FILE__);

$maxSafeCount = (isset($_REQUEST["force"]) && $_REQUEST["force"] == "Y"? false: COption::GetOptionString("controller", "safe_count"));
$cnt = 0;
$sTableID = "tbl_controller_run";
$lAdmin = new CAdminList($sTableID);

if ($query <> "" && check_thurly_sessid())
{
	$lAdmin->BeginPrologContent();
	$arFilter = array(
		"DISCONNECTED" => "N",
		"CONTROLLER_GROUP_ID" => $_REQUEST['controller_group_id'],
	);

	if ($member_id > 0)
	{
		$arFilter["=ID"] = $member_id;
	}
	elseif (isset($_REQUEST['controller_member_id']) && trim($_REQUEST['controller_member_id']) != "")
	{
		if (!is_array($_REQUEST['controller_member_id']))
			$IDs = array_map("trim", explode(" ", $_REQUEST['controller_member_id']));
		else
			$IDs = array_map("trim", $_REQUEST['controller_member_id']);

		$arFilterID = array();
		$arFilterNAME = array();

		foreach ($IDs as $id)
		{
			if (is_numeric($id))
				$arFilterID[] = $id;
			else
				$arFilterNAME[] = strtoupper($id);
		}

		if (!empty($arFilterID) || !empty($arFilterNAME))
		{
			$arFilter[0] = array("LOGIC" => "OR");
			if (!empty($arFilterID))
				$arFilter[0]["=ID"] = $arFilterID;
			if (!empty($arFilterNAME))
				$arFilter[0]["NAME"] = $arFilterNAME;
		}
	}

	$runQueue = array();
	$dbr_members = CControllerMember::GetList(Array("ID" => "ASC"), $arFilter);
	while ($ar_member = $dbr_members->Fetch())
	{
		$runQueue[$ar_member["ID"]] = $ar_member["NAME"];
		$cnt++;
		if ($maxSafeCount !== false && $cnt > $maxSafeCount)
		{
			$runQueue = array();
			break;
		}
	}

	$cnt_ok = 0;
	foreach ($runQueue as $memberId => $memberName)
	{
		if ($_REQUEST['add_task'] == "Y")
		{
			if (CControllerTask::Add(array(
				"TASK_ID" => "REMOTE_COMMAND",
				"CONTROLLER_MEMBER_ID" => $memberId,
				"INIT_EXECUTE" => $query,
			))
			)
			{
				$cnt_ok++;
			}
		}
		else
		{
			echo BeginNote();
			echo "<b>".htmlspecialcharsEx($memberName).":</b><br>";
			$result = CControllerMember::RunCommandWithLog($memberId, $query);
			if ($result === false)
			{
				$e = $APPLICATION->GetException();
				echo "Error: ".$e->GetString();
			}
			else
			{
				echo nl2br($result);
			}
			echo EndNote();
		}
	}

	if ($maxSafeCount !== false && $cnt > $maxSafeCount)
	{
		echo BeginNote();
		echo GetMessage("CTRLR_RUN_ERR_TOO_MANY_SELECTED");
		echo EndNote();
		?>
		<script>top.document.getElementById('tr_force').style.display = '';</script><?
	}
	else
	{
		if ($cnt <= 0)
		{
			echo BeginNote();
			echo GetMessage("CTRLR_RUN_ERR_NSELECTED");
			echo EndNote();
		}

		if ($_REQUEST['add_task'] == "Y")
		{
			echo BeginNote();
			echo GetMessage("CTRLR_RUN_SUCCESS", array(
				"#SUCCESS_CNT#" => $cnt_ok,
				"#CNT#" => $cnt,
				"#LANG#" => LANGUAGE_ID,
			));
			echo EndNote();
		}
	}

	$lAdmin->EndPrologContent();
}

$lAdmin->BeginEpilogContent();
?>
	<input type="hidden" name="query" id="query" value="<?=htmlspecialcharsbx($query)?>">
	<input type="hidden" name="controller_member_id" id="controller_member_id" value="<?=htmlspecialcharsbx($controller_member_id)?>">
	<input type="hidden" name="add_task" id="add_task" value="<?=htmlspecialcharsbx($_REQUEST['add_task'])?>">
	<input type="hidden" name="controller_group_id" id="controller_group_id" value="<?=htmlspecialcharsbx($controller_group_id)?>">
	<input type="hidden" name="force" id="force" value="N">
<?
$lAdmin->EndEpilogContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CTRLR_RUN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
?>
	<script>
		function __FPHPSubmit()
		{
			if (confirm('<?echo GetMessage("CTRLR_RUN_CONFIRM")?>'))
			{
				document.getElementById('query').value = document.getElementById('php').value;
				if (document.getElementById('fcontroller_member_id'))
					document.getElementById('controller_member_id').value = document.getElementById('fcontroller_member_id').value;
				if (document.getElementById('fcontroller_group_id'))
					document.getElementById('controller_group_id').value = document.getElementById('fcontroller_group_id').value;
				document.getElementById('add_task').value = (document.getElementById('fadd_task').checked ? 'Y' : 'N');
				document.getElementById('force').value = (document.getElementById('fforce').checked ? 'Y' : 'N');

				window.scrollTo(0, 500);
				<?=$lAdmin->ActionPost($APPLICATION->GetCurPageParam("mode=frame", Array("mode", "PAGEN_1")))?>
			}
		}
	</script>
<?
$aTabs = array(
	array(
		"DIV" => "tab1",
		"TAB" => GetMessage("CTRLR_RUN_COMMAND_FIELD"),
		"TITLE" => GetMessage("CTRLR_RUN_COMMAND_TAB_TITLE"),
	),
);
$editTab = new CAdminTabControl("editTab", $aTabs);
?>
	<form name="form1" action="<? echo $APPLICATION->GetCurPage() ?>" method="POST">
		<input type="hidden" name="lang" value="<?=LANG?>">
		<?
		if ($member_id > 0)
		{
			echo GetMessage("CTRLR_RUN_FILTER_SITE").': #'.$member_id."<br><br>";
		}
		else
		{
			$arGroups = Array();
			$dbr_groups = CControllerGroup::GetList(Array("SORT" => "ASC", "NAME" => "ASC", "ID" => "ASC"));
			while ($ar_groups = $dbr_groups->GetNext())
			{
				$arGroups[$ar_groups["ID"]] = $ar_groups["NAME"];
			}

			$filter = new CAdminFilter(
				$sTableID."_filter_id",
				Array(GetMessage("CTRLR_RUN_FILTER_GROUP"))
			);

			$filter->Begin();
			?>
			<tr>
				<td nowrap><label
						for="fcontroller_member_id"><?= GetMessage("CTRLR_RUN_FILTER_SITE") ?></label>:
				</td>
				<td nowrap>
					<?
					$dbr_members = CControllerMember::GetList(array(
						"SORT" => "ASC",
						"NAME" => "ASC",
						"ID" => "ASC",
					), array(
						"DISCONNECTED" => "N",
					), array(
						"ID",
						"NAME",
					), array(
					), array(
						"nTopCount" => $maxSafeCount+1,
					));
					$arMembers = array();
					$c = 0;
					while ($ar_member = $dbr_members->Fetch())
					{
						$arMembers[$ar_member["ID"]] = $ar_member["NAME"];
						$c++;
						if ($maxSafeCount !== false && $c > $maxSafeCount)
						{
							$arMembers = array();
							break;
						}
					}

					if ($arMembers):?>
						<select name="fcontroller_member_id" id="fcontroller_member_id">
							<option value=""><? echo GetMessage("CTRLR_RUN_FILTER_SITE_ALL") ?></option>
							<? foreach ($arMembers as $ID => $NAME): ?>
								<option
									value="<? echo htmlspecialcharsbx($ID) ?>"
									<? if ($controller_member_id == $ID) echo ' selected'; ?>
								><? echo htmlspecialcharsEx($NAME." [".$ID."]") ?></option>
							<? endforeach ?>
						</select>
						<?
					else:?>
						<input
							type="text"
							name="fcontroller_member_id"
							id="fcontroller_member_id"
							value="<? echo htmlspecialcharsbx($controller_member_id) ?>"
							size="47"
						/>
					<? endif ?>
				</td>
			</tr>
			<tr>
				<td nowrap>
					<label for="fcontroller_group_id"><? echo htmlspecialcharsEx(GetMessage("CTRLR_RUN_FILTER_GROUP")) ?></label>:
				</td>
				<td nowrap><? echo htmlspecialcharsEx($controller_group_id) ?>
					<select name="fcontroller_group_id" id="fcontroller_group_id">
						<option value=""><? echo GetMessage("CTRLR_RUN_FILTER_GROUP_ANY") ?></option>
						<? foreach ($arGroups as $group_id => $group_name): ?>
							<option
								value="<?= $group_id ?>"
								<? if ($group_id == $controller_group_id) echo "selected" ?>
							><?= $group_name ?></option>
						<? endforeach; ?>
					</select>
				</td>
			</tr>
			<?
			$filter->Buttons();
			$filter->End();
		}
		?>


		<?=thurly_sessid_post()?>
		<?
		$editTab->Begin();
		$editTab->BeginNextTab();
		?>
		<tr>
			<td>
				<input type="hidden" name="lang" value="<?=LANG?>">
				<textarea name="php" id="php" rows="15" style="width:100%;" title=""><? echo htmlspecialcharsbx($query); ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input type="checkbox" id="fadd_task" name="fadd_task" title="<? echo GetMessage("CTRLR_RUN_ADD_TASK_LABEL") ?>" value="Y">
				<label for="fadd_task" title="<? echo GetMessage("CTRLR_RUN_ADD_TASK_LABEL") ?>">
					<? echo GetMessage("CTRLR_RUN_ADD_TASK") ?></label>
			</td>
		</tr>
		<tr style="display:none" id="tr_force">
			<td>
				<input type="checkbox" id="fforce" name="fforce" value="Y">
				<label for="fforce"><? echo GetMessage("CTRLR_RUN_FORCE_RUN") ?></label>
			</td>
		</tr>
		<? $editTab->Buttons(); ?>
		<input
			type="button"
			accesskey="x"
			name="execute"
			value="<? echo GetMessage("CTRLR_RUN_BUTT_RUN") ?>"
			onclick="return __FPHPSubmit();"
			class="adm-btn-save"
			<? if (!$bCanRunCommand) echo 'disabled="disabled"' ?>
		>
		<input type="reset" value="<? echo GetMessage("CTRLR_RUN_BUTT_CLEAR") ?>">
		<?
		$editTab->End();
		?>
	</form>
<?
if (COption::GetOptionString('fileman', "use_code_editor", "Y") == "Y" && CModule::IncludeModule('fileman'))
{
	CCodeEditor::Show(array(
		'textareaId' => 'php',
		'height' => 350,
		'forceSyntax' => 'php',
	));
}
?>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
