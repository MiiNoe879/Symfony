<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var \Thurly\Bizproc\Activity\PropertiesDialog $dialog */

$runtimeData = $dialog->getRuntimeData();
$accountId = $runtimeData['ACCOUNT_ID'];
$audienceId = $runtimeData['AUDIENCE_ID'];
$autoRemoveDayNumber = $runtimeData['AUTO_REMOVE_DAY_NUMBER'];
$provider = $runtimeData['PROVIDER'];
$type = htmlspecialcharsbx($provider['TYPE']);

$containerNodeId = 'crm-robot-ads-container-' . $type;
$destroyEventName = 'crm-robot-ads-destroy';
?>

	<script>

		BX.ready(function ()
		{
			var dialog = BX.Crm.Automation.Runtime.getRobotSettingsDialog();
			if (!dialog)
			{
				return;
			}

			BX.remove(BX('<?=$containerNodeId?>'));
			var containerNode = BX.create('div');
			containerNode.id = '<?=$containerNodeId?>';
			dialog.form.appendChild(containerNode);

			BX.addCustomEvent(dialog.popup, 'onPopupClose', function(){
				BX.onCustomEvent(window, '<?=$destroyEventName?>');
			});
		});

	</script>

<?
global $APPLICATION;
$APPLICATION->IncludeComponent(
	'thurly:crm.ads.retargeting',
	'',
	array(
		'CONTAINER_NODE_ID' => $containerNodeId,
		'PROVIDER' => $provider,
		'ACCOUNT_ID' => $accountId,
		'AUDIENCE_ID' => $audienceId,
		'AUTO_REMOVE_DAY_NUMBER' => $autoRemoveDayNumber,
		'JS_DESTROY_EVENT_NAME' => $destroyEventName
	)
);
?>