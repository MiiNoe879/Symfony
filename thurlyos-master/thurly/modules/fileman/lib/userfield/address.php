<?php
namespace Thurly\Fileman\UserField;


use Thurly\ThurlyOS\RestrictionCounter;
use Thurly\Main\Config\Option;
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);


class Address extends \Thurly\Main\UserField\TypeBase
{
	const USER_TYPE_ID = 'address';

	const THURLY24_RESTRICTION = 100;
	const THURLY24_RESTRICTION_CODE = 'uf_address';

	protected static $restrictionCount = null;

	function getUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => static::USER_TYPE_ID,
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_ADDRESS_DESCRIPTION"),
			"BASE_TYPE" => \CUserTypeManager::BASE_TYPE_STRING,
			"EDIT_CALLBACK" => array(__CLASS__, 'getPublicEdit'),
			"VIEW_CALLBACK" => array(__CLASS__, 'getPublicView'),
		);
	}

	public static function getApiKey()
	{
		$apiKey = Option::get('fileman', 'google_map_api_key', '');
		if(Loader::includeModule('thurlyos'))
		{
			if(\CThurlyOS::isCustomDomain())
			{
				$apiKey = '';
			}

			$key = Option::get('thurlyos', 'google_map_api_key', '');
			$keyHost = Option::get('thurlyos', 'google_map_api_key_host', '');
			if(strlen($keyHost) > 0)
			{
				if($keyHost === BX24_HOST_NAME)
				{
					$apiKey = $key;
				}
			}
		}

		return $apiKey;
	}

	public static function getApiKeyHint()
	{
		$hint = '';
		if(static::getApiKey() === '')
		{
			if(Loader::includeModule('thurlyos'))
			{
				if(\CThurlyOS::isCustomDomain())
				{
					$hint = Loc::getMessage(
						'USER_TYPE_ADDRESS_NO_KEY_HINT_B24',
						array(
							'#settings_path#' => \CThurlyOS::PATH_CONFIGS
						)
					);
				}
			}
			else
			{
				if(defined('ADMIN_SECTION') && ADMIN_SECTION === true)
				{
					$settingsPath = '/thurly/admin/settings.php?lang='.LANGUAGE_ID.'&mid=fileman';
				}
				else
				{
					$settingsPath = SITE_DIR.'configs/';
				}

				if(
					!file_exists($_SERVER['DOCUMENT_ROOT'].$settingsPath)
					|| !is_dir($_SERVER['DOCUMENT_ROOT'].$settingsPath)
				)
				{
					$settingsPath = SITE_DIR.'settings/configs/';
				}

				$hint = Loc::getMessage(
					'USER_TYPE_ADDRESS_NO_KEY_HINT',
					array(
						'#settings_path#' => $settingsPath
					)
				);
			}
		}

		return $hint;
	}

	public static function getTrialHint()
	{
		if(static::useRestriction() && !static::checkRestriction())
		{
			\CThurlyOS::initLicenseInfoPopupJS(static::THURLY24_RESTRICTION_CODE);

			return array(
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL_TITLE'),
				Loc::getMessage('USER_TYPE_ADDRESS_TRIAL'),
			);
		}
		else
		{
			return false;
		}
	}

	public static function canUseMap()
	{
		return static::getApiKey() !== '' && static::checkRestriction();
	}

	public static function checkRestriction()
	{
		if(
			static::useRestriction()
			&& static::$restrictionCount === null
			&& Loader::includeModule('thurlyos')
		)
		{
			static::$restrictionCount = RestrictionCounter::get(static::THURLY24_RESTRICTION_CODE);
		}

		return static::$restrictionCount < static::THURLY24_RESTRICTION;
	}

	public static function useRestriction()
	{
		return Loader::includeModule('thurlyos') && !\CThurlyOS::IsLicensePaid() && !\CThurlyOS::IsNfrLicense();
	}

	function PrepareSettings($arUserField)
	{
		return array(
			"SHOW_MAP" => $arUserField["SETTINGS"]["SHOW_MAP"] === 'N' ? 'N' : 'Y',
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "text";
		}
	}

	function CheckFields($arUserField, $value)
	{
		return array();
	}

	function OnBeforeSave($arUserField, $value)
	{
		if(static::useRestriction() && static::checkRestriction() && strlen($value) > 0 && strpos($value, '|') >= 0)
		{
			if($arUserField['MULTIPLE'] === 'Y')
			{
				$increment = !is_array($arUserField['VALUE']) || !in_array($value, $arUserField['VALUE']);
			}
			else
			{
				$increment = $arUserField['VALUE'] !== $value;
			}

			if($increment && Loader::includeModule('thurlyos'))
			{
				RestrictionCounter::increment(static::THURLY24_RESTRICTION_CODE);
			}
		}

		return $value;
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';
		if($bVarsFromForm)
		{
			$value = $GLOBALS[$arHtmlControl["NAME"]]["SHOW_MAP"] === 'N' ? 'N' : 'Y';
		}
		elseif(is_array($arUserField))
		{
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"] === 'N' ? 'N' : 'Y';
		}
		else
		{
			$value = "Y";
		}
		$result .= '
		<tr>
			<td>'.GetMessage("USER_TYPE_ADDRESS_SHOW_MAP").':</td>
			<td>
				<input type="hidden" name="'.$arHtmlControl["NAME"].'[SHOW_MAP]" value="N">
				<label><input type="checkbox" name="'.$arHtmlControl["NAME"].'[SHOW_MAP]" value="Y" '.($value === 'Y' ? ' checked="checked"' : '').'> '.GetMessage('MAIN_YES').'</label>
			</td>
		</tr>
		';

		/// start position

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		return static::getEdit($arUserField, $arHtmlControl);
	}

	function GetEditFormHtmlMulty($arUserField, $arHtmlControl)
	{
		return static::getEdit($arUserField, $arHtmlControl);
	}

	protected static function getEdit($arUserField, $arHtmlControl)
	{
		$html = '';
		\CJSCore::Init('userfield_address', 'google_map');

		if(static::canUseMap())
		{
			ob_start();

			$controlId = $arUserField['FIELD_NAME'];
?>
<div id="<?=$controlId?>"></div>
<span style="display: none;" id="<?=HtmlFilter::encode($arUserField['FIELD_NAME'])?>_result"></span>
<script>
	(function(){
		'use strict';

		var control = new BX.Fileman.UserField.Address(BX('<?=$controlId?>'), {
			value: <?=\CUtil::PhpToJsObject(static::normalizeFieldValue($arUserField['VALUE']))?>,
			multiple: <?=$arUserField['MULTIPLE'] === 'Y' ? 'true' : 'false'?>
		});
		BX.addCustomEvent(control, 'UserFieldAddress::Change', function(value)
		{
			var node = BX('<?=\CUtil::JSEscape($arUserField['FIELD_NAME'])?>_result');
			var html = '';
			if(value.length === 0)
			{
				value = [{text:''}];
			}

			for(var i = 0; i < value.length; i++)
			{
				var inputValue = value[i].text;

				if(!!value[i].coords)
				{
					inputValue += '|' + value[i].coords.join(';');
				}

				html += '<input type="hidden" name="<?=$arHtmlControl['NAME']?>" value="'+BX.util.htmlspecialchars(inputValue)+'" />';
			}

			node.innerHTML = html;
		});
	})();
</script>
<?
			$html = ob_get_clean();
		}
		else
		{
			$value = static::normalizeFieldValue($arUserField['VALUE']);

			$first = true;
			foreach($value as $res)
			{
				if(!$first)
				{
					$html .= static::getHelper()->getMultipleValuesSeparator();
				}
				$first = false;

				list($text, $coords) = static::parseValue($res);

				$attrList = array(
					'type' => 'text',
					'class' => static::getHelper()->getCssClassName(),
					'name' => $arHtmlControl['NAME'],
					'value' => $text,
				);

				if(static::useRestriction() && !static::checkRestriction())
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressSearchRestriction.show(this)';
				}
				elseif(static::getApiKey() === '')
				{
					$attrList['onfocus'] = 'BX.Fileman.UserField.addressKeyRestriction.show(this)';
				}

				$html .= static::getHelper()->wrapSingleField('<input '.static::buildTagAttributes($attrList).'/>');
			}

			if($arUserField["MULTIPLE"] == "Y")
			{
				$html .= static::getHelper()->getCloneButton($arHtmlControl['NAME']);
			}
		}

		return $html;
	}

	public static function getPublicEdit($arUserField, $arAdditionalParameters = array())
	{
		$fieldName = static::getFieldName($arUserField, $arAdditionalParameters);
		$arUserField['VALUE'] = static::getFieldValue($arUserField, $arAdditionalParameters);

		$html = static::getEdit($arUserField, array(
			'NAME' => $fieldName,
		));

		static::initDisplay();

		return static::getHelper()->wrapDisplayResult($html);
	}

	public static function getPublicView($arUserField, $arAdditionalParameters = array())
	{
		$value = static::normalizeFieldValue($arUserField["VALUE"]);

		$html = '';
		$first = true;

		foreach($value as $res)
		{
			if(strlen($res) > 0)
			{
				if(!$first)
				{
					$html .= static::getHelper()->getMultipleValuesSeparator();
				}

				$first = false;

				list($text, $coords) = static::parseValue($res);

				if(strlen($text) > 0)
				{
					if(!$arAdditionalParameters['printable'] && $coords && static::getApiKey() !== '')
					{
						$res = '<a href="javascript:void(0)" onmouseover="BX.Fileman.UserField.addressSearchResultDisplayMap.showHover(this, '.HtmlFilter::encode(\CUtil::PhpToJSObject(array('text' => $text, 'coords' => $coords))).');" onmouseout="BX.Fileman.UserField.addressSearchResultDisplayMap.closeHover(this)">'.HtmlFilter::encode($text).'</a>';
					}
					else
					{
						$res = HtmlFilter::encode($text);
					}

					$html .= static::getHelper()->wrapSingleField($res);
				}
			}
		}

		static::initDisplay(array('userfield_address', 'google_map'));

		return static::getHelper()->wrapDisplayResult($html);
	}

	protected static function parseValue($value)
	{
		$coords = '';
		if(strpos($value, '|') >= 0)
		{
			list($value, $coords) = explode('|', $value);
			if(strlen($coords) > 0)
			{
				$coords = explode(';', $coords);
			}
		}

		return array($value, $coords);
	}
}