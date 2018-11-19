<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Thurly\Main\Localization\Loc;
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var CThurlyComponent $component */


$rowTemplate = <<<HTML

<tr>
	<td class="task-time-date-column"><span class="task-time-date">{{date}}</span></td>
	<td class="task-time-author-column">
		<a class="task-log-author" href="{{pathToUserProfile}}" target="_top">{{userName}}</a>
		<span class="task-log-author-text">{{userName}}</span>
	</td>
</tr>

HTML;
?>

<table id="task-time-table" class="task-time-table<?if ($arParams["PUBLIC_MODE"]):?> task-time-table-public<?endif?>">
	<col class="task-time-date-column" />
	<col class="task-time-author-column" />
	<tr>
		<th class="task-time-date-column"><?=Loc::getMessage("TASKS_ELAPSED_DATE")?></th>
		<th class="task-time-author-column"><?=Loc::getMessage("TASKS_ELAPSED_AUTHOR")?></th>
	</tr>
	<?
	$records = array();

	if(!empty($arResult['TEMPLATE_DATA']['DATA']['EFFECTIVE']['ITEMS']))
	{
		foreach ($arResult['TEMPLATE_DATA']['DATA']['EFFECTIVE']['ITEMS'] as $row)
		{
			$date = new \Thurly\Tasks\Util\Type\DateTime($row['DATETIME']);

			echo str_replace(
				array(
					"{{id}}",
					"{{date}}",
					"{{userId}}",
					"{{userType}}",
					"{{userName}}",
					"{{pathToUserProfile}}"
				),
				array(
					"id" => $row["ID"],
					"date" => $date,
					"userId"=>$row['USER_ID'],
					"userType"=>$row['USER_TYPE'],
					"userName" => htmlspecialcharsbx(\Thurly\Tasks\Util\User::getUserName($row['USER_ID'])),
					'pathToUserProfile'=>CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $row["USER_ID"]))
				),
				$rowTemplate
			);
		}
	}
	?>
</table>