<?php
use Thurly\Main\Context;
use Thurly\Main\Text\HtmlFilter;
use Thurly\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
	<head>
		<?$APPLICATION->ShowHead();?>
		<style type="text/css">
			.content-wrap {
				padding: 10px 0 10px 0;
			}
			.content {
				margin: 0;
			}
		</style>
	</head>
	<body class="<?$APPLICATION->showProperty("BodyClass")?> crm-webform-iframe">
		<div class="content-wrap">
			<div class="content">
				<?if(!empty($arResult['ERRORS'])):
					foreach($arResult['ERRORS'] as $error)
					{
						ShowError($error);
					}
					?>
					<script>
						BX.ready(function(){
							BX.CrmWebForm = new CrmWebForm({});
						});
					</script>
					<?
				else:
					$this->getComponent()->includeWebFormTemplate();
				endif;?>
			</div>
		</div>
	</body>
</html>
<?