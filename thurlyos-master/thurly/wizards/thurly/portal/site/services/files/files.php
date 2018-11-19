<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp"))
{
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp",
		WIZARD_SITE_PATH,
		$rewrite = false,
		$recursive = true,
		$delete_after_copy = true,
		$exclude = "thurly"
	);

	unlink(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp");
}
if(!(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011 && WIZARD_FIRST_INSTAL !== "Y") || WIZARD_B24_TO_CP)
{
	if (WIZARD_INSTALL_DEMO_DATA || WIZARD_FIRST_INSTAL !== "Y" || WIZARD_B24_TO_CP)
	{
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/",
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "thurly"
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/thurly/modules/intranet/install/public/',
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "thurlyos"
		);

		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."pub/"), Array("*" => "R"));

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/thurly/modules/rest/install/public/marketplace/',
			WIZARD_SITE_PATH."/marketplace/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."marketplace/"), Array("*" => "R"));

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/thurly/modules/rest/install/public/apconnect/',
			WIZARD_SITE_PATH."/apconnect/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
		WizardServices::SetFilePermission(Array(WIZARD_SITE_ID, WIZARD_SITE_DIR."apconnect/"), Array("*" => "R"));

		if (WIZARD_B24_TO_CP)
		{
			if (file_exists(WIZARD_SITE_PATH."settings/"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."settings/");
			if (file_exists(WIZARD_SITE_PATH."marketplace/"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."marketplace/");
			if (file_exists(WIZARD_SITE_PATH."thurly/templates/login"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."thurly/templates/login");
			if (file_exists(WIZARD_SITE_PATH."company/meeting"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."company/meeting");	
		}
	}
}

if (WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011)
	CopyDirFiles(WIZARD_ABSOLUTE_PATH."/site/public/.department.menu_ext.php", WIZARD_SITE_PATH."/.department.menu_ext.php", false);

$dateTimeFormat = (LANGUAGE_ID == "en") ? "F j, Y h:i a" : ((LANGUAGE_ID == "de") ? "j. F Y H:i:s" : "d.m.Y H:i:s");
$dateFormat = (LANGUAGE_ID == "en") ? "F j, Y" : ((LANGUAGE_ID == "de") ? "j. F Y" : "d.m.Y");
$dateFormatNoYear = (LANGUAGE_ID == "en") ? "F j" : ((LANGUAGE_ID == "de") ? "j. F" : "d.m");
CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_DIR" => WIZARD_SITE_DIR, "DATE_TIME_FORMAT" => $dateTimeFormat, "DATE_FORMAT" => $dateFormat, "DATE_FORMAT_NO_YEAR" => $dateFormatNoYear));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/desktop.php", Array("SITE_ID" => WIZARD_SITE_ID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", Array("SITE_ID" => WIZARD_SITE_ID));

if (WIZARD_INSTALL_DEMO_DATA || WIZARD_B24_TO_CP)
{
	$arUrlRewrite = array();
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}

	$arNewUrlRewrite = array(
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:photogallery_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/personal/mail/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:intranet.mail.config",
			"PATH"	=>	WIZARD_SITE_DIR."company/personal/mail/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/personal/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:socialnetwork_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/personal.php",
		),
		/*array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."community/forum/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:forum",
			"PATH"	=>	WIZARD_SITE_DIR."community/forum.php",
		),*/
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."about/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:photogallery",
			"PATH"	=>	WIZARD_SITE_DIR."about/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."workgroups/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:socialnetwork_group",
			"PATH"	=>	WIZARD_SITE_DIR."workgroups/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/lists/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:lists",
			"PATH"	=>	WIZARD_SITE_DIR."services/lists/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/faq/#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:support.faq",
			"PATH"	=>	WIZARD_SITE_DIR."services/faq/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."services/idea/#",
			"RULE" => "",
			"ID" => "thurly:idea",
			"PATH" => WIZARD_SITE_DIR."services/idea/index.php"
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."tasks/getfile/(\\d+)/(\\d+)/([^/]+)#",
			"RULE" => "taskid=$1&fileid=$2&filename=$3",
			"ID" => "thurly:tasks_tools_getfile",
			"PATH" => WIZARD_SITE_DIR."tasks/getfile.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/pub/#",
			"RULE" => "",
			"ID" => "thurly:disk.external.link",
			"PATH" => WIZARD_SITE_DIR."docs/pub/extlinks.php"
		),

		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."/docs/all#",
			"RULE"	=>	"",
			"ID"	=>	"thurly:disk.aggregator",
			"PATH"	=>	WIZARD_SITE_DIR."docs/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/sale/#",
			"RULE" => "",
			"ID" => "thurly:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/sale/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/shared#",
			"RULE" => "",
			"ID" => "thurly:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/shared/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."docs/manage/#",
			"RULE" => "",
			"ID" => "thurly:disk.common",
			"PATH" => WIZARD_SITE_DIR."docs/manage/index.php",
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'bizproc/processes/#',
			'RULE' => '',
			'ID' => 'thurly:lists',
			'PATH' => WIZARD_SITE_DIR.'bizproc/processes/index.php'
		),
		array(
			'CONDITION' => '#^'.WIZARD_SITE_DIR.'pub/form/([0-9a-z_]+?)/([0-9a-z]+?)/.*#',
			'RULE' => 'form_code=$1&sec=$2',
			'ID' => 'thurly:crm.webform.fill',
			'PATH' => WIZARD_SITE_DIR.'pub/form.php'
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."crm/button/#",
			"RULE" => "",
			"ID" => "thurly:crm.button",
			"PATH" => WIZARD_SITE_DIR."crm/button/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."onec/#",
			"RULE" => "",
			"ID" => "thurly:crm.1c.start",
			"PATH" => WIZARD_SITE_DIR."onec/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."settings/configs/userconsent/#",
			"RULE" => "",
			"ID" => "thurly:intranet.userconsent",
			"PATH" => WIZARD_SITE_DIR."configs/userconsent.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/#",
			"RULE" => "",
			"ID" => "thurly:rest.marketplace",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/local/#",
			"RULE" => "",
			"ID" => "thurly:rest.marketplace.localapp",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/local/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/app/#",
			"RULE" => "",
			"ID" => "thurly:app.layout",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/app/index.php",
		),

		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."marketplace/hook/#",
			"RULE" => "",
			"ID" => "thurly:rest.hook",
			"PATH" =>  WIZARD_SITE_DIR."marketplace/hook/index.php",
		)
	);
	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			CUrlRewriter::Add($arUrl);
		}
	}
}
?>