<?
use Thurly\Main;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Location\Admin\Helper;

define("NO_AGENT_CHECK", true);
define("NO_KEEP_STATISTIC", true);

$initialTime = time();
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/prolog.php");

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_IMPORT_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

// check for indexes
$indexes = \Thurly\Sale\Location\Import\ImportProcess::getIndexMap();
$absent = array();
if(is_array($indexes) && !empty($indexes))
{
	foreach($indexes as $name => $params)
	{
		if((string) $params['TABLE'] != '' && !$params['DROP_ONLY'])
		{
			if(!\Thurly\Sale\Location\DB\Helper::checkIndexNameExists($name, $params['TABLE']))
				$absent[] = 'create index '.$name.' on '.$params['TABLE'].' ('.implode(', ', $params['COLUMNS']).')'.\Thurly\Sale\Location\DB\Helper::getQuerySeparatorSql();
		}
	}
}

if(!empty($absent))
{
	?>

	<span style="color: #ff0000">
		<?=Loc::getMessage('SALE_LOCATION_IMPORT_NO_INDEXES_WARNING', array(
			'#ANCHOR_SQL_CONSOLE#' => '<a href="/thurly/admin/sql.php" target="_blank">',
			'#ANCHOR_END#' => '</a>'
		))?>
	</span>
	<br />
	<br />

	<pre>
<?=(implode("\n", $absent));?>
	</pre>

	<?
}
else
{
	$APPLICATION->IncludeComponent(
		'thurly:sale.location.import',
		'admin',
		array(
			'PATH_TO_IMPORT' => Helper::getImportUrl(),
			'INITIAL_TIME' => $initialTime
		),
		false
	);
}
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");?>
