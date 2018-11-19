<?php

\Thurly\Main\Loader::registerAutoLoadClasses(
	"disk",
	array(
		"disk" => "install/index.php",
		"thurly\\disk\\document\\blankfiledata" => "lib/document/blankfiledata.php",
		"thurly\\disk\\document\\documentcontroller" => "lib/document/documentcontroller.php",
		"thurly\\disk\\document\\documenthandler" => "lib/document/documenthandler.php",
		"thurly\\disk\\document\\filedata" => "lib/document/filedata.php",
		"thurly\\disk\\document\\googlehandler" => "lib/document/googlehandler.php",
		"thurly\\disk\\document\\googleviewerhandler" => "lib/document/googleviewerhandler.php",
		"thurly\\disk\\document\\localdocumentcontroller" => "lib/document/localdocumentcontroller.php",
		"thurly\\disk\\document\\onedrivehandler" => "lib/document/onedrivehandler.php",
		"thurly\\disk\\document\\startpage" => "lib/document/startpage.php",
		"thurly\\disk\\internals\\error\\error" => "lib/internals/error/error.php",
		"thurly\\disk\\internals\\error\\errorcollection" => "lib/internals/error/errorcollection.php",
		"thurly\\disk\\internals\\error\\ierrorable" => "lib/internals/error/ierrorable.php",
		"thurly\\disk\\internals\\attachedobject" => "lib/internals/attachedobject.php",
		"thurly\\disk\\internals\\basecomponent" => "lib/internals/basecomponent.php",
		"thurly\\disk\\internals\\controller" => "lib/internals/controller.php",
		"thurly\\disk\\internals\\datamanager" => "lib/internals/datamanager.php",
		"thurly\\disk\\internals\\deletedlog" => "lib/internals/deletedlog.php",
		"thurly\\disk\\internals\\diag" => "lib/internals/diag.php",
		"thurly\\disk\\internals\\diskcomponent" => "lib/internals/diskcomponent.php",
		"thurly\\disk\\internals\\editsession" => "lib/internals/editsession.php",
		"thurly\\disk\\internals\\externallink" => "lib/internals/externallink.php",
		"thurly\\disk\\internals\\file" => "lib/internals/file.php",
		"thurly\\disk\\internals\\folder" => "lib/internals/folder.php",
		"thurly\\disk\\internals\\model" => "lib/internals/model.php",
		"thurly\\disk\\internals\\object" => "lib/internals/object.php",
		"thurly\\disk\\internals\\objectpath" => "lib/internals/objectpath.php",
		"thurly\\disk\\internals\\recentlyused" => "lib/internals/recentlyused.php",
		"thurly\\disk\\internals\\right" => "lib/internals/right.php",
		"thurly\\disk\\internals\\sharing" => "lib/internals/sharing.php",
		"thurly\\disk\\internals\\simpleright" => "lib/internals/simpleright.php",
		"thurly\\disk\\internals\\storage" => "lib/internals/storage.php",
		"thurly\\disk\\internals\\tmpfile" => "lib/internals/tmpfile.php",
		"thurly\\disk\\internals\\version" => "lib/internals/version.php",
		"thurly\\disk\\internals\\volume" => "lib/internals/volume.php",
		"thurly\\disk\\proxytype\\base" => "lib/proxytype/base.php",
		"thurly\\disk\\proxytype\\common" => "lib/proxytype/common.php",
		"thurly\\disk\\proxytype\\group" => "lib/proxytype/group.php",
		"thurly\\disk\\proxytype\\user" => "lib/proxytype/user.php",
		"thurly\\disk\\security\\disksecuritycontext" => "lib/security/disksecuritycontext.php",
		"thurly\\disk\\security\\fakesecuritycontext" => "lib/security/fakesecuritycontext.php",
		"thurly\\disk\\security\\securitycontext" => "lib/security/securitycontext.php",
		"thurly\\disk\\uf\\blogpostcommentconnector" => "lib/uf/blogpostcommentconnector.php",
		"thurly\\disk\\uf\\blogpostconnector" => "lib/uf/blogpostconnector.php",
		"thurly\\disk\\uf\\calendareventconnector" => "lib/uf/calendareventconnector.php",
		"thurly\\disk\\uf\\connector" => "lib/uf/connector.php",
		"thurly\\disk\\uf\\controller" => "lib/uf/controller.php",
		"thurly\\disk\\uf\\documentcontroller" => "lib/uf/documentcontroller.php",
		"thurly\\disk\\uf\\fileusertype" => "lib/uf/fileusertype.php",
		"thurly\\disk\\uf\\forummessageconnector" => "lib/uf/forummessageconnector.php",
		"thurly\\disk\\uf\\isupportforeignconnector" => "lib/uf/isupportforeignconnector.php",
		"thurly\\disk\\uf\\localdocumentcontroller" => "lib/uf/localdocumentcontroller.php",
		"thurly\\disk\\uf\\sonetcommentconnector" => "lib/uf/sonetcommentconnector.php",
		"thurly\\disk\\uf\\sonetlogconnector" => "lib/uf/sonetlogconnector.php",
		"thurly\\disk\\uf\\stubconnector" => "lib/uf/stubconnector.php",
		"thurly\\disk\\uf\\taskconnector" => "lib/uf/taskconnector.php",
		"thurly\\disk\\uf\\crmconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmdealconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmleadconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmcompanyconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmcontactconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmmessageconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\crmmessagecommentconnector" => "lib/uf/crmconnector.php",
		"thurly\\disk\\uf\\userfieldmanager" => "lib/uf/userfieldmanager.php",
		"thurly\\disk\\uf\\versionusertype" => "lib/uf/versionusertype.php",
		"thurly\\disk\\ui\\avatar" => "lib/ui/avatar.php",
		"thurly\\disk\\ui\\destination" => "lib/ui/destination.php",
		"thurly\\disk\\ui\\icon" => "lib/ui/icon.php",
		"thurly\\disk\\ui\\lazyload" => "lib/ui/lazyload.php",
		"thurly\\disk\\ui\\text" => "lib/ui/text.php",
		"thurly\\disk\\ui\\viewer" => "lib/ui/viewer.php",
		"thurly\\disk\\attachedobject" => "lib/attachedobject.php",
		"thurly\\disk\\bizprocdocument" => "lib/bizprocdocument.php",
		"thurly\\disk\\deletedlog" => "lib/deletedlog.php",
		"thurly\\disk\\desktop" => "lib/desktop.php",
		"thurly\\disk\\configuration" => "lib/configuration.php",
		"thurly\\disk\\userconfiguration" => "lib/configuration.php",
		"thurly\\disk\\downloadcontroller" => "lib/downloadcontroller.php",
		"thurly\\disk\\driver" => "lib/driver.php",
		"thurly\\disk\\editsession" => "lib/editsession.php",
		"thurly\\disk\\externallink" => "lib/externallink.php",
		"thurly\\disk\\file" => "lib/file.php",
		"thurly\\disk\\filelink" => "lib/filelink.php",
		"thurly\\disk\\folder" => "lib/folder.php",
		"thurly\\disk\\specificfolder" => "lib/folder.php",
		"thurly\\disk\\folderlink" => "lib/folderlink.php",
		"thurly\\disk\\baseobject" => "lib/baseobject.php",
		"thurly\\disk\\right" => "lib/right.php",
		"thurly\\disk\\rightsmanager" => "lib/rightsmanager.php",
		"thurly\\disk\\sharing" => "lib/sharing.php",
		"thurly\\disk\\simpleright" => "lib/simpleright.php",
		"thurly\\disk\\socialnetworkhandlers" => "lib/socialnetworkhandlers.php",
		"thurly\\disk\\storage" => "lib/storage.php",
		"thurly\\disk\\systemuser" => "lib/systemuser.php",
		"thurly\\disk\\typefile" => "lib/typefile.php",
		"thurly\\disk\\urlmanager" => "lib/urlmanager.php",
		"thurly\\disk\\user" => "lib/user.php",
		"thurly\\disk\\version" => "lib/version.php",
	)
);


CJSCore::RegisterExt('disk', array(
	'js' => '/thurly/js/disk/c_disk.js',
	'css' => '/thurly/js/disk/css/disk.css',
	'lang' => BX_ROOT.'/modules/disk/lang/'.LANGUAGE_ID.'/js_disk.php',
	'rel' => array('core', 'popup', 'ajax', 'fx', 'dd'),
	'oninit' => function()
	{
		$isCompositeMode = defined("USE_HTML_STATIC_CACHE") && USE_HTML_STATIC_CACHE === true;
		if($isCompositeMode)
		{
			// It's a hack. The package "disk" can be included in static area and pasted in <head>.
			// It means that every page has this BX.messages in composite cache. But we have user's depended options in BX.messages.
			// And in this case we'll rewrite composite cache and have invalid data in composite cache.
			// So in this way we have to insert BX.messages in dynamic area by viewContent placeholders.
			global $APPLICATION;
			$APPLICATION->AddViewContent("pagetitle", '
				<script>
					BX.message["disk_restriction"] = false;
					BX.message["disk_revision_api"] = ' . (int)\Thurly\Disk\Configuration::getRevisionApi() . ';
					BX.message["disk_document_service"] = "' . (string)\Thurly\Disk\UserConfiguration::getDocumentServiceCode() . '";
					BX.message["wd_desktop_disk_is_installed"] = ' . (\Thurly\Disk\Desktop::isDesktopDiskInstall()? 'true' : 'false') . ';
				</script>    
			');
		}
		else
		{
			return array(
				'lang_additional' => array(
					'disk_restriction' => false,
					'disk_revision_api' => (int)\Thurly\Disk\Configuration::getRevisionApi(),
					'disk_document_service' => (string)\Thurly\Disk\UserConfiguration::getDocumentServiceCode(),
					'wd_desktop_disk_is_installed' => (bool)\Thurly\Disk\Desktop::isDesktopDiskInstall(),
				),
			);
		}
	},
));

CJSCore::RegisterExt('file_dialog', array(
	'js' => '/thurly/js/disk/file_dialog.js',
	'css' => '/thurly/js/disk/css/file_dialog.css',
	'lang' => '/thurly/modules/disk/lang/'.LANGUAGE_ID.'/install/js/file_dialog.php',
	'rel' => array('core', 'popup', 'json', 'ajax', 'disk',),
));

CJSCore::RegisterExt('disk_desktop', array(
	'js' => '/thurly/js/disk/disk_desktop.js',
	'lang' => '/thurly/modules/disk/lang/'.LANGUAGE_ID.'/install/js/disk_desktop.php',
	'rel' => array('core',),
));

CJSCore::RegisterExt('disk_tabs', array(
	'js' => '/thurly/js/disk/tabs.js',
	'css' => '/thurly/js/disk/css/tabs.css',
	'rel' => array('core', 'disk',),
));

CJSCore::RegisterExt('disk_queue', array(
	'js' => '/thurly/js/disk/queue.js',
	'rel' => array('core', 'disk',),
));

CJSCore::RegisterExt('disk_external_loader', array(
	'js' => '/thurly/js/disk/external_loader.js',
	'rel' => array('core', 'disk', 'disk_queue'),
));

CJSCore::RegisterExt('disk_information_popups', array(
	'js' => '/thurly/js/disk/information_popups.js',
	'lang' => '/thurly/modules/disk/lang/'.LANGUAGE_ID.'/install/js/information_popups.php',
	'rel' => array('core', 'disk'),
));
