<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var $arParams array
 * @var $arResult array
 * @var $this CThurlyComponent
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

if (!CModule::IncludeModule('voximplant'))
	return;



if (in_array(LANGUAGE_ID, Array("ru", "kz", "ua", "by")))
{
	if (IsModuleInstalled('thurlyos'))
	{
		$arResult['LINK_TO_DOC'] = "https://dev.1c-thurly.ru/learning/course/index.php?COURSE_ID=52&CHAPTER_ID=02459";
	}
	else
	{
		$arResult['LINK_TO_DOC'] = "https://dev.1c-thurly.ru/learning/course/index.php?COURSE_ID=48&CHAPTER_ID=02459";
	}
}
else
{
	if (IsModuleInstalled('thurlyos'))
	{
		$arResult['LINK_TO_DOC'] = "http://www.thurlysoft.com/support/training/course/index.php?COURSE_ID=55&LESSON_ID=7121";
	}
	else
	{
		$arResult['LINK_TO_DOC'] = "http://www.thurlysoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=7121";
	}
}

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();


return $arResult;
?>