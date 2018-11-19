<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true){die();};?>

<?if(\Thurly\Tasks\Util\DisposableAction::needConvertTemplateFiles()):?>
	<?
	$GLOBALS['APPLICATION']->IncludeComponent("thurly:tasks.util.process",
		'',
		array(
		),
		false,
		array("HIDE_ICONS" => "Y")
	);
	?>
	<?=\Thurly\Main\Update\Stepper::getHtml("tasks");?>
<?endif?>