<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var CThurlyComponentTemplate $this */
/** @var array $arResult */

\Thurly\Main\Mail\EventMessageThemeCompiler::includeComponent(
	"thurly:catalog.show.products.mail",
	$this->getName(),
	array(
		"LIST_ITEM_ID" => $arResult['ITEMS']
	)
);