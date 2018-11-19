<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 */

?>
<span class="fields string">
<?
$first = true;
foreach ($arResult["VALUE"] as $res)
{
	if (!$first)
	{
		?><span class="fields separator"></span><?
	}
	else
	{
		$first = false;
	}

	if (StrLen($arParams['arUserField']['PROPERTY_VALUE_LINK']) > 0)
	{
		$res = '<a href="'.htmlspecialcharsbx(str_replace('#VALUE#', urlencode($res), $arParams['arUserField']['PROPERTY_VALUE_LINK'])).'">'.$res.'</a>';
	}

?><span class="fields string"><?=$res?></span><?

}
?>
</span>

