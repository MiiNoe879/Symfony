<?
// delete from updates
//include("module_updater.php");

CModule::AddAutoloadClasses(
	"mobileapp",
	array(
		"CMobile"                => "classes/general/mobile.php",
		"CAdminMobileDetail"     => "classes/general/interface.php",
		"CAdminMobileDetailTmpl" => "classes/general/interface.php",
		"CAdminMobileMenu"       => "classes/general/interface.php",
		"CAdminMobileFilter"     => "classes/general/filter.php",
		"CMobileLazyLoad"        => "classes/general/interface.php",
		"CAdminMobileEdit"       => "classes/general/interface.php",
		"CMobileAppPullSchema"   => "classes/general/pull.php",
		"CAdminMobilePush"       => "classes/general/push.php",
	)
);

$GLOBALS["APPLICATION"]->AddJSKernelInfo('mobileapp', array('/thurly/js/mobileapp/fastclick.js'));

CJSCore::RegisterExt('mobile_webrtc', array(
		'js'   => '/thurly/js/mobileapp/mobile_webrtc.js',
		'lang' => '/thurly/modules/mobileapp/lang/'.LANGUAGE_ID.'/mobile_webrtc.php',
		'rel'=>array("ajax","ls")
	));

CJSCore::RegisterExt('mdesigner', array(
		'js'   => '/thurly/js/mobileapp/designer.js',
		'css'  => '/thurly/js/mobileapp/app_designer.css',
		'lang' => '/thurly/modules/mobileapp/lang/'.LANGUAGE_ID.'/mobile_designer.php',
		'rel' => array('ajax','popup','qrcode')
	));

CJSCore::RegisterExt('mobile_fastclick', array(
		'js'   => '/thurly/js/mobileapp/fastclick.js',
	));
CJSCore::RegisterExt('mobile_gesture', array(
		'js'   => '/thurly/js/mobileapp/gesture.js',
	));

?>
