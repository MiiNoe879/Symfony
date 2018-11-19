<?
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_admin_before.php');

$readOnly = $APPLICATION->GetGroupRight('sale') < 'W';

if ($readOnly)
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/sale/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/sale/include.php');

use	Thurly\Sale\BusinessValue;
use	Thurly\Sale\Helpers\Admin\BusinessValueControl;
use Thurly\Sale\Internals\BusinessValueTable;
use Thurly\Sale\Internals\BusinessValuePersonDomainTable;
use	Thurly\Sale\Internals\Input;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/sale/lib/helpers/admin/businessvalue.php');

$isSuccess = true;

$businessValueControl = new BusinessValueControl('bizval');

if ($_SERVER['REQUEST_METHOD'] == 'POST'
	&& ! $readOnly
	&& check_thurly_sessid()
	&& ($_POST['save'] || $_POST['apply']))
{
	if ($isSuccess = $businessValueControl->setMapFromPost())
		$businessValueControl->saveMap();
}

$filter = BusinessValueControl::getFilter(isset($_GET['del_filter']) ? null : ($_GET['FILTER'] ?: $_POST['FILTER']));
$filter['HIDE_FILLED_CODES'] = false;

// VIEW ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(Loc::getMessage('BIZVAL_PAGE_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_admin_after.php');

if (! $isSuccess)
{
	call_user_func(function () {
		$m = new CAdminMessage(Loc::getMessage('BIZVAL_PAGE_ERRORS'));
		echo $m->Show();
	});
}

?>
	<form name="bizvalFilter" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
		<?

		$filterControl = new CAdminFilter('bizvalFilterControl',
			array(
				Loc::getMessage('BIZVAL_PAGE_CODES'),
				//Loc::getMessage('BIZVAL_PAGE_VALUES'), // TODO later
			)
		);

		$filterControl->Begin()

		?>
		<tr>
			<td><?=Loc::getMessage('BIZVAL_PAGE_CODES')?>:</td>
			<td>
				<?

				echo Input\Manager::getEditHtml('FILTER[CONSUMER_KEY]', BusinessValueControl::getConsumerInput(), $filter['CONSUMER_KEY']);

				/* TODO later
				$consumerCodeInput = BusinessValueControl::getConsumerCodeInput();

				if ($consumerCodeInput[$filter['CONSUMER_KEY']])
					echo Input\Manager::getEditHtml('FILTER[CODE_KEY]', $consumerCodeInput[$filter['CONSUMER_KEY']], $filter['CODE_KEY']);
				*/

				?>
			</td>
		</tr>
		<?

		/* TODO later

		?>
		<tr>
			<td><?=Loc::getMessage('BIZVAL_PAGE_VALUES')?>:</td>
			<td>
				<?

				$providerInput = BusinessValueControl::getProviderInput(null); // TODO null - personTypeId
				$providerInput['OPTIONS'] = array('' => Loc::getMessage('BIZVAL_PAGE_ALL')) + $providerInput['OPTIONS'];
				$providerInput['ONCHANGE'] = "bizvalChangeProvider(this, '', true)"; // TODO null - personTypeId

				echo Input\Manager::getEditHtml('FILTER[PROVIDER_KEY]', $providerInput, $filter['PROVIDER_KEY']);

				echo Input\Manager::getEditHtml('FILTER[PROVIDER_VALUE]'
					, BusinessValueControl::getValueInput(null, $filter['PROVIDER_KEY']) // TODO null - personTypeId
					, $filter['PROVIDER_VALUE']
				);

				?>
			</td>
		</tr>
		<?

		*/

		$filterControl->Buttons(
			array(
				//"table_id" => $sTableID,
				'url' => $APPLICATION->GetCurPage(),
				'form' => 'bizvalFilter'
			)
		);

		$filterControl->End();

		?>
	</form>

	<form method="POST"
	      id="bizvalTabs_form"
	      name="bizvalTabs_form"
	      action="<?=$APPLICATION->GetCurPage().'?lang='.LANGUAGE_ID.GetFilterParams('filter_', false)?>"
	      enctype="multipart/form-data">

		<?=thurly_sessid_post()?>

		<input type="hidden" name="FILTER[CODE_KEY]" value="<?=$filter['CODE_KEY']?>">
		<input type="hidden" name="FILTER[CONSUMER_KEY]" value="<?=$filter['CONSUMER_KEY']?>">
		<input type="hidden" name="FILTER[PROVIDER_KEY]" value="<?=$filter['PROVIDER_KEY']?>">
		<input type="hidden" name="FILTER[PROVIDER_VALUE]" value="<?=$filter['PROVIDER_VALUE']?>">

		<?$businessValueControl->renderMap($filter)?>

		<div class="adm-detail-content-btns-wrap">
			<div class="adm-detail-content-btns">
				<?

				$hkInst = CHotKeys::getInstance();
				echo '<input'.($aParams["disabled"] === true? " disabled":"")
						.' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'
						.GetMessage("admin_lib_edit_apply_title").$hkInst->GetTitle("Edit_Apply_Button").'" class="adm-btn-save" />';
				echo $hkInst->PrintJSExecs($hkInst->GetCodeByClassName("Edit_Apply_Button"));

				?>
			</div>
		</div>

	</form>
<?

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_admin.php');
