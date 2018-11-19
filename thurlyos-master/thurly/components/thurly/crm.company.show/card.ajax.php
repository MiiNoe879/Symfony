<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if(!CModule::IncludeModule('crm'))
	return ;

global $APPLICATION;

$CCrmPerms = CCrmPerms::GetCurrentUserPermissions();
if (!(CCrmPerms::IsAuthorized() && CCrmCompany::CheckReadPermission(0, $CCrmPerms)))
	return;

$arResult = array();
$_GET['USER_ID'] = preg_replace('/^(CONTACT|COMPANY|LEAD|DEAL)_/i'.BX_UTF_PCRE_MODIFIER, '', $_GET['USER_ID']);
$iCompanyId = (int) $_GET['USER_ID'];
if ($iCompanyId > 0)
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'?company_id=#company_id#&show');
	$arParams['PATH_TO_COMPANY_EDIT'] = CrmCheckPath('PATH_TO_COMPANY_EDIT', $arParams['PATH_TO_COMPANY_EDIT'], $APPLICATION->GetCurPage().'?company_id=#company_id#&edit');
	$arResult['COMPANY_TYPE_LIST'] = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
	$arResult['EMPLOYEES_LIST'] = CCrmStatus::GetStatusListEx('EMPLOYEES');

	$obRes = CCrmCompany::GetList(array(), array('ID' => $iCompanyId));
	$arCompany = $obRes->Fetch();
	if ($arCompany == false)
		return ;

	$arCompany['PATH_TO_COMPANY_SHOW'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_SHOW'],
		array(
			'company_id' => $iCompanyId
		)
	);
	$arCompany['PATH_TO_COMPANY_EDIT'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_EDIT'],
		array(
			'company_id' => $iCompanyId
		)
	);

	//region Multifields
	$arEntityTypes = CCrmFieldMulti::GetEntityTypes();
	$multiFieldHtml = array();

	$sipConfig =  array(
		'ENABLE_SIP' => true,
		'SIP_PARAMS' => array(
			'ENTITY_TYPE' => 'CRM_'.CCrmOwnerType::CompanyName,
			'ENTITY_ID' => $iCompanyId)
	);

	$dbRes = CCrmFieldMulti::GetListEx(
		array(),
		array('ENTITY_ID' => CCrmOwnerType::CompanyName, 'ELEMENT_ID' => $iCompanyId, '@TYPE_ID' => array('PHONE', 'EMAIL', 'WEB')),
		false,
		false,
		array('TYPE_ID', 'VALUE_TYPE', 'VALUE')
	);

	while($multiField = $dbRes->Fetch())
	{
		$typeID = isset($multiField['TYPE_ID']) ? $multiField['TYPE_ID'] : '';

		if(isset($multiFieldHtml[$typeID]))
		{
			continue;
		}

		$value = isset($multiField['VALUE']) ? $multiField['VALUE'] : '';
		$valueType = isset($multiField['VALUE_TYPE']) ? $multiField['VALUE_TYPE'] : '';

		$entityType = $arEntityTypes[$typeID];
		$valueTypeInfo = isset($entityType[$valueType]) ? $entityType[$valueType] : null;

		$params = array('VALUE' => $value, 'VALUE_TYPE_ID' => $valueType, 'VALUE_TYPE' => $valueTypeInfo);
		$item = CCrmViewHelper::PrepareMultiFieldValueItemData($typeID, $params, $sipConfig);
		if(isset($item['value']) && $item['value'] !== '')
		{
			$multiFieldHtml[$typeID] = $item['value'];
		}
	}
	//endregion

	$strCard = '
<div class="bx-user-info-data-cont-video  bx-user-info-fields" id="bx_user_info_data_cont_1">
	<div class="bx-user-info-data-name ">
		<a href="'.$arCompany['PATH_TO_COMPANY_SHOW'].'" target="_blank">'.htmlspecialcharsbx($arCompany['TITLE']).'</a>
	</div>
	<div class="bx-user-info-data-info">';
	if (!empty($arCompany['COMPANY_TYPE']))
	{
		$strCard .= '
		<span class="field-name">'.GetMessage('CRM_COLUMN_COMPANY_TYPE').'</span>:
		<span class="fields enumeration">'.$arResult['COMPANY_TYPE_LIST'][$arCompany['COMPANY_TYPE']].'</span>
		<br />';
	}
	if (!empty($arCompany['EMPLOYEES']))
	{
		$strCard .= '
		<span class="field-name">'.GetMessage('CRM_COLUMN_EMPLOYEES').'</span>:
		<span class="fields enumeration">'.$arResult['EMPLOYEES_LIST'][$arCompany['EMPLOYEES']].'</span>
		<br />';
	}
	$strCard .= '<span class="field-name">'.GetMessage('CRM_COLUMN_DATE_MODIFY').'</span>:
		<span class="fields enumeration">'.FormatDate('x', MakeTimeStamp($arCompany['DATE_MODIFY']), (time() + CTimeZone::GetOffset())).'</span>
		<br />
		<br />
	</div>
	<div class="bx-user-info-data-name bx-user-info-seporator">
		<nobr>'.GetMessage('CRM_SECTION_COMPANY_INFO').'</nobr>
	</div>
	<div class="bx-user-info-data-info">';
	if (isset($multiFieldHtml['PHONE']))
	{
		$strCard .= '
		<span class="field-name">'.GetMessage('CRM_COLUMN_PHONE').'</span>:
		<span class="crm-client-contacts-block-text crm-client-contacts-block-handset">'.$multiFieldHtml['PHONE'].'</span>
		<br />';
	}
	if (isset($multiFieldHtml['EMAIL']))
	{
		$strCard .= '
		<span class="field-name">'.GetMessage('CRM_COLUMN_EMAIL').'</span>:
		<span class="crm-client-contacts-block-text">'.$multiFieldHtml['EMAIL'].'</span>
		<br />';
	}
	if (isset($multiFieldHtml['WEB']))
	{
		$strCard .= '
		<span class="field-name">'.GetMessage('CRM_COLUMN_WEB').'</span>:
		<span class="crm-client-contacts-block-text">'.$multiFieldHtml['WEB'].'</span>
		<br />';
	}
	$strCard .= '</div>
</div>';

	if (!empty($arCompany['LOGO']))
	{
		$imageFile = CFile::GetFileArray($arCompany['LOGO']);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array('width' => 102, 'height' => 104),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false
			);
			$imageImg = CFile::ShowImage($arFileTmp['src'], 102, 104, "border='0'", '');
		}
		if (strlen($imageImg)>0)
			$strPhoto = '<a href="'.$arCompany['PATH_TO_COMPANY_SHOW'].'" class="bx-user-info-data-photo" target="_blank">'.$imageImg.'</a>';
		else
			$strPhoto = '<a href="'.$arCompany['PATH_TO_COMPANY_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';
	}
	else
		$strPhoto = '<a href="'.$arCompany['PATH_TO_COMPANY_SHOW'].'" class="bx-user-info-data-photo no-photo" target="_blank"></a>';

	$strToolbar2 = '
<div class="bx-user-info-data-separator"></div>
<ul>
	<li class="bx-icon-show">
		<a href="'.$arCompany['PATH_TO_COMPANY_SHOW'].'" target="_blank">'.GetMessage('CRM_OPER_SHOW').'</a>
	</li>
	<li class="bx-icon bx-icon-message">
		<a href="'.$arCompany['PATH_TO_COMPANY_EDIT'].'" target="_blank">'.GetMessage('CRM_OPER_EDIT').'</a>
	</li>
</ul>';

	$script = '
		var params = 
		{
			serviceUrls: 
			{ 
				"CRM_'.CUtil::JSEscape(CCrmOwnerType::CompanyName).'" : 
					"/thurly/components/thurly/crm.company.show/ajax.php?'.thurly_sessid_get().'"
			},
			messages: 
			{
				"unknownRecipient": "'.GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT').'",
				"makeCall": "'.GetMessageJS('CRM_SIP_MGR_MAKE_CALL').'"
			}
		};
		
		if(typeof(BX.CrmSipManager) === "undefined")
		{
			BX.loadScript(
				"/thurly/js/crm/common.js", 
				function() { BX.CrmSipManager.ensureInitialized(params); }
			);
		}
		else
		{
			BX.CrmSipManager.ensureInitialized(params);
		}';

	$arResult = array(
		'Toolbar' => '',
		'ToolbarItems' => '',
		'Toolbar2' => $strToolbar2,
		'Card' => $strCard,
		'Photo' => $strPhoto,
		'Scripts' => array($script)
	);
}

$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
echo CUtil::PhpToJsObject(array('RESULT' => $arResult));
if(!defined('PUBLIC_AJAX_MODE'))
{
	define('PUBLIC_AJAX_MODE', true);
}
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
die();

?>
