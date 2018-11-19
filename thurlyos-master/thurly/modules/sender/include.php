<?php

CModule::AddAutoloadClasses('sender', array(

		"thurly\\sender\\contactlisttable" => "lib/contact.php",
		"thurly\\sender\\listtable" => "lib/contact.php",

		"thurly\\sender\\groupconnectortable" => "lib/group.php",

		"thurly\\sender\\mailinggrouptable" => "lib/mailing.php",
		"Thurly\\Sender\\MailingSubscriptionTable" => "lib/mailing.php",

		"thurly\\sender\\postingrecipienttable" => "lib/posting.php",
		"thurly\\sender\\postingreadtable" => "lib/posting.php",
		"thurly\\sender\\postingclicktable" => "lib/posting.php",
		"thurly\\sender\\postingunsubtable" => "lib/posting.php",
));


\CJSCore::RegisterExt("sender_admin", Array(
	"js" =>    "/thurly/js/sender/admin.js",
	"lang" =>    "/thurly/modules/sender/lang/" . LANGUAGE_ID . "/js_admin.php",
	"rel" =>   array()
));

CJSCore::RegisterExt('sender_stat', array(
	'js' => array(
		'/thurly/js/main/amcharts/3.3/amcharts.js',
		'/thurly/js/main/amcharts/3.3/serial.js',
		'/thurly/js/main/amcharts/3.3/themes/light.js',
		'/thurly/js/sender/heatmap/script.js',
		'/thurly/js/sender/stat/script.js'
	),
	'css' => array(
		'/thurly/js/sender/stat/style.css'
	),
	'rel' => array('core', 'ajax', 'date')
));