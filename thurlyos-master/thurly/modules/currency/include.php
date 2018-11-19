<?
use Thurly\Main\Loader;

global $DB;
$strDBType = strtolower($DB->type);

Loader::registerAutoLoadClasses(
	'currency',
	array(
		'CCurrency' => 'general/currency.php',
		'CCurrencyLang' => 'general/currency_lang.php',
		'CCurrencyRates' => $strDBType.'/currency_rate.php',
		'\Thurly\Currency\Compatible\Tools' => 'lib/compatible/tools.php',
		'\Thurly\Currency\Helpers\Admin\Tools' => 'lib/helpers/admin/tools.php',
		'\Thurly\Currency\Helpers\Editor' => 'lib/helpers/editor.php',
		'\Thurly\Currency\UserField\Money' => 'lib/userfield/money.php',
		'\Thurly\Currency\CurrencyManager' => 'lib/currencymanager.php',
		'\Thurly\Currency\CurrencyTable' => 'lib/currency.php',
		'\Thurly\Currency\CurrencyLangTable' => 'lib/currencylang.php',
		'\Thurly\Currency\CurrencyRateTable' => 'lib/currencyrate.php',
		'\Thurly\Currency\CurrencyClassifier' => 'lib/currencyclassifier.php'
	)
);
unset($strDBType);

\CJSCore::RegisterExt(
	'currency',
	array(
		'js' => '/thurly/js/currency/core_currency.js',
		'rel' => array('core')
	)
);

\CJSCore::RegisterExt(
	'core_money_editor',
	array(
		'js' => '/thurly/js/currency/core_money_editor.js',
		'oninit' => function()
		{
			return array(
				'lang_additional' => array(
					'CURRENCY' => \Thurly\Currency\Helpers\Editor::getListCurrency(),
				),
			);
		}
	)
);

\CJSCore::RegisterExt(
	'core_uf_money',
	array(
		'js' => '/thurly/js/currency/core_uf_money.js',
		'css' => '/thurly/js/currency/css/core_uf_money.css',
		'rel' => array('uf', 'core_money_editor'),
	)
);


define('CURRENCY_CACHE_DEFAULT_TIME', 10800);
define('CURRENCY_ISO_STANDART_URL', 'http://www.iso.org/iso/home/standards/currency_codes.htm');

/*
* @deprecated deprecated since currency 14.0.0
* @see CCurrencyLang::CurrencyFormat()
*/
function CurrencyFormat($price, $currency)
{
	return CCurrencyLang::CurrencyFormat($price, $currency, true);
}

/*
* @deprecated deprecated since currency 14.0.0
* @see CCurrencyLang::CurrencyFormat()
*/
function CurrencyFormatNumber($price, $currency)
{
	return CCurrencyLang::CurrencyFormat($price, $currency, false);
}