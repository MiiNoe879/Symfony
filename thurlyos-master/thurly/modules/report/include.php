<?
global $DBType;

CModule::AddAutoloadClasses(
	'report',
	array(
		'CReport' => 'classes/general/report.php',
		'CReportHelper' => 'classes/general/report_helper.php',
		'BXUserException' => 'classes/general/report.php',
		'BXFormException' => 'classes/general/report.php',
		'Thurly\Report\ReportTable' => 'lib/report.php',
		'\Thurly\Report\ReportTable' => 'lib/report.php',
	)
);

CJSCore::RegisterExt('report', array(
	'js' => '/thurly/js/report/js/report.js',
	'css' => '/thurly/js/report/css/report.css',
	'lang' => BX_ROOT.'/modules/report/lang/'.LANGUAGE_ID.'/install/js/report.php',
	'rel' => array('core', 'popup', 'json', 'ajax')
));
