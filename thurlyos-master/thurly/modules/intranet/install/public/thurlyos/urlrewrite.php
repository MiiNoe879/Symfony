<?
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/pub/site/(.*?)#",
		"RULE" => "path=\$1",
		"ID" => "thurly:landing.pub",
		"PATH" => "/pub/site/index.php",
	),
	array(
		"CONDITION" => "#^/docs/pub/(?<hash>[0-9a-f]{32})/(?<action>[0-9a-zA-Z]+)/\\?#",
		"RULE" => "hash=\$1&action=\$2&",
		"ID" => "thurly:disk.external.link",
		"PATH" => "/docs/pub/index.php",
	),
	array(
		"CONDITION" => "#^/disk/(?<action>[0-9a-zA-Z]+)/(?<fileId>[0-9]+)/\\?#",
		"RULE" => "action=\$1&fileId=\$2&",
		"ID" => "thurly:disk.services",
		"PATH" => "/thurly/services/disk/index.php",
	),
	array(
		"CONDITION" => "#^/pub/pay/([0-9a-zA-Z_-]+)/([0-9a-zA-Z]+)/([^/]*)#",
		"RULE" => "account_number=\$1&hash=\$2",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/docs/pub/(?<hash>[0-9a-f]{32})/(?<action>.*)\$#",
		"RULE" => "hash=\$1&action=\$2",
		"ID" => "thurly:disk.external.link",
		"PATH" => "/docs/pub/index.php",
	),
	array(
		"CONDITION" => "#^/pub/(?<hash>[0-9a-f]{32})/(?<action>.*)\$#",
		"RULE" => "hash=\$1&action=\$2",
		"ID" => "thurly:disk.external.link",
		"PATH" => "/pub/index.php",
	),
	array(
		"CONDITION" => "#^/pub/pay/([\\w\\W]+)/([0-9a-zA-Z]+)/([^/]*)#",
		"RULE" => "account_number=\$1&hash=\$2",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/pub/pay/([0-9]+)/([0-9a-zA-Z]+)/([^/]*)#",
		"RULE" => "account_number=\$1&hash=\$2",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/pub/pay/([0-9]+)/([0-9a-zA-Z]+)/([^/]+)#",
		"RULE" => "account_number=\$1&hash=\$2",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/pub/form/([0-9a-z_]+?)/([0-9a-z]+?)/.*#",
		"RULE" => "form_code=\$1&sec=\$2",
		"ID" => "thurly:crm.webform.fill",
		"PATH" => "/pub/form.php",
	),
	array(
		"CONDITION" => "#^/mobile/disk/(?<hash>[0-9]+)/download#",
		"RULE" => "download=1&objectId=\$1",
		"ID" => "thurly:mobile.disk.file.detail",
		"PATH" => "/mobile/disk/index.php",
	),
	array(
		"CONDITION" => "#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#",
		"RULE" => "alias=\$1",
		"PATH" => "/desktop_app/router.php",
	),
	array(
		"CONDITION" => "#^/tasks/getfile/(\\d+)/(\\d+)/([^/]+)#",
		"RULE" => "taskid=\$1&fileid=\$2&filename=\$3",
		"ID" => "thurly:tasks_tools_getfile",
		"PATH" => "/tasks/getfile.php",
	),
	array(
		"CONDITION" => "#^/pub/pay/([0-9]+)/([0-9a-zA-Z]+)/#",
		"RULE" => "account_number=\$1&hash=\$2",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/extranet/contacts/personal/#",
		"RULE" => "",
		"ID" => "thurly:socialnetwork_user",
		"PATH" => "/extranet/contacts/personal.php",
	),
	array(
		"CONDITION" => "#^/extranet/workgroups/create/#",
		"RULE" => "",
		"ID" => "thurly:extranet.group_create",
		"PATH" => "/extranet/workgroups/create/index.php",
	),
	array(
		"CONDITION" => "#^/extranet/crm/configs/perms/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.perms",
		"PATH" => "/extranet/crm/configs/perms/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/deal_category/#",
		"RULE" => "",
		"ID" => "thurly:crm.deal_category",
		"PATH" => "/crm/configs/deal_category/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/productprops/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.productprops",
		"PATH" => "/crm/configs/productprops/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/mailtemplate/#",
		"RULE" => "",
		"ID" => "thurly:crm.mail_template",
		"PATH" => "/crm/configs/mailtemplate/index.php",
	),
	array(
		"CONDITION" => "#^/thurly/services/ymarket/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/thurly/services/ymarket/index.php",
	),
	array(
		"CONDITION" => "#^/extranet/crm/configs/bp/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.bp",
		"PATH" => "/extranet/crm/configs/bp/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/locations/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.locations",
		"PATH" => "/crm/configs/locations/index.php",
	),
	array(
		"CONDITION" => "#^/extranet/mobile/webdav#",
		"RULE" => "",
		"ID" => "thurly:mobile.webdav.file.list",
		"PATH" => "/extranet/mobile/webdav/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/mycompany/#",
		"RULE" => "",
		"ID" => "thurly:crm.company",
		"PATH" => "/crm/configs/mycompany/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/currency/#",
		"RULE" => "",
		"ID" => "thurly:crm.currency",
		"PATH" => "/crm/configs/currency/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/measure/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.measure",
		"PATH" => "/crm/configs/measure/index.php",
	),
	array(
		"CONDITION" => "#^/extranet/docs/shared#",
		"RULE" => "",
		"ID" => "thurly:webdav",
		"PATH" => "/extranet/docs/index.php",
	),
	array(
		"CONDITION" => "#^/extranet/workgroups/#",
		"RULE" => "",
		"ID" => "thurly:socialnetwork_group",
		"PATH" => "/extranet/workgroups/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/exch1c/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.exch1c",
		"PATH" => "/crm/configs/exch1c/index.php",
	),
	array(
		"CONDITION" => "#^/crm/reports/report/#",
		"RULE" => "",
		"ID" => "thurly:crm.report",
		"PATH" => "/crm/reports/report/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/fields/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.fields",
		"PATH" => "/crm/configs/fields/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/preset/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.preset",
		"PATH" => "/crm/configs/preset/index.php",
	),
	array(
		"CONDITION" => "#^/marketplace/local/#",
		"RULE" => "",
		"ID" => "thurly:rest.marketplace.localapp",
		"PATH" => "/marketplace/local/index.php",
	),
	array(
		"CONDITION" => "#^/marketplace/hook/#",
		"RULE" => "",
		"ID" => "thurly:rest.hook",
		"PATH" => "/marketplace/hook/index.php",
	),
	array(
		"CONDITION" => "#^/online/(/?)([^/]*)#",
		"RULE" => "",
		"PATH" => "/desktop_app/router.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/perms/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.perms",
		"PATH" => "/crm/configs/perms/index.php",
	),
	array(
		"CONDITION" => "#^/bizproc/processes/#",
		"RULE" => "",
		"ID" => "thurly:lists",
		"PATH" => "/bizproc/processes/index.php",
	),
	array(
		"CONDITION" => "#^/company/personal/mail/#",
		"RULE" => "",
		"ID" => "thurly:intranet.mail.config",
		"PATH" => "/company/personal/mail/index.php",
	),
	array(
		"CONDITION" => "#^/company/personal/#",
		"RULE" => "",
		"ID" => "thurly:socialnetwork_user",
		"PATH" => "/company/personal.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/tax/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.tax",
		"PATH" => "/crm/configs/tax/index.php",
	),
	array(
		"CONDITION" => "#^/marketplace/app/#",
		"RULE" => "",
		"ID" => "thurly:app.layout",
		"PATH" => "/marketplace/app/index.php",
	),
	array(
		"CONDITION" => "#^/timeman/meeting/#",
		"RULE" => "",
		"ID" => "thurly:meetings",
		"PATH" => "/timeman/meeting/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/ps/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.ps",
		"PATH" => "/crm/configs/ps/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/automation/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.automation",
		"PATH" => "/crm/configs/automation/index.php",
	),
	array(
		"CONDITION" => "#^/crm/configs/bp/#",
		"RULE" => "",
		"ID" => "thurly:crm.config.bp",
		"PATH" => "/crm/configs/bp/index.php",
	),
	array(
		"CONDITION" => "#^/company/lists/#",
		"RULE" => "",
		"ID" => "thurly:lists",
		"PATH" => "/company/lists/index.php",
	),
	array(
		"CONDITION" => "#^/crm/activity/#",
		"RULE" => "",
		"ID" => "thurly:crm.activity",
		"PATH" => "/crm/activity/index.php",
	),
	array(
		"CONDITION" => "#^/mobile/webdav#",
		"RULE" => "",
		"ID" => "thurly:mobile.webdav.file.list",
		"PATH" => "/mobile/webdav/index.php",
	),
	array(
		"CONDITION" => "#^/crm/company/#",
		"RULE" => "",
		"ID" => "thurly:crm.company",
		"PATH" => "/crm/company/index.php",
	),
	array(
		"CONDITION" => "#^/crm/webform/#",
		"RULE" => "",
		"ID" => "thurly:crm.webform",
		"PATH" => "/crm/webform/index.php",
	),
	array(
		"CONDITION" => "#^/\\.well-known#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/thurly/groupdav.php",
	),
	array(
		"CONDITION" => "#^/marketplace/#",
		"RULE" => "",
		"ID" => "thurly:rest.marketplace",
		"PATH" => "/marketplace/index.php",
	),
	array(
		"CONDITION" => "#^/crm/invoice/#",
		"RULE" => "",
		"ID" => "thurly:crm.invoice",
		"PATH" => "/crm/invoice/index.php",
	),
	array(
		"CONDITION" => "#^/crm/product/#",
		"RULE" => "",
		"ID" => "thurly:crm.product",
		"PATH" => "/crm/product/index.php",
	),
	array(
		"CONDITION" => "#^/crm/contact/#",
		"RULE" => "",
		"ID" => "thurly:crm.contact",
		"PATH" => "/crm/contact/index.php",
	),
	array(
		"CONDITION" => "#^/workgroups/#",
		"RULE" => "",
		"ID" => "thurly:socialnetwork_group",
		"PATH" => "/workgroups/index.php",
	),
	array(
		"CONDITION" => "#^/crm/button/#",
		"RULE" => "",
		"ID" => "thurly:crm.button",
		"PATH" => "/crm/button/index.php",
	),
	array(
		"CONDITION" => "#^/crm/quote/#",
		"RULE" => "",
		"ID" => "thurly:crm.quote",
		"PATH" => "/crm/quote/index.php",
	),
	array(
		"CONDITION" => "#^/crm/lead/#",
		"RULE" => "",
		"ID" => "thurly:crm.lead",
		"PATH" => "/crm/lead/index.php",
	),
	array(
		"CONDITION" => "#^/crm/deal/#",
		"RULE" => "",
		"ID" => "thurly:crm.deal",
		"PATH" => "/crm/deal/index.php",
	),
	array(
		"CONDITION" => "#^/docs/pub/#",
		"RULE" => "",
		"ID" => "thurly:webdav.extlinks",
		"PATH" => "/docs/pub/extlinks.php",
	),
	array(
		"CONDITION" => "#^/m/docs/#",
		"RULE" => "",
		"ID" => "thurly:mobile.webdav.aggregator",
		"PATH" => "/m/docs/index.php",
	),
	array(
		"CONDITION" => "#^/rest/#",
		"RULE" => "",
		"PATH" => "/thurly/services/rest/index.php",
	),
	array(
		"CONDITION" => "#^/docs/#",
		"RULE" => "",
		"ID" => "thurly:webdav",
		"PATH" => "/docs/index.php",
	),
	array(
		"CONDITION" => "#^/pub/#",
		"RULE" => "",
		"ID" => "thurly:crm.invoice.payment.client",
		"PATH" => "/pub/payment.php",
	),
	array(
		"CONDITION" => "#^/stssync/contacts/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/contacts/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/calendar/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/calendar/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/tasks/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/tasks/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/contacts_crm/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/contacts_crm/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/contacts_extranet/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/contacts_extranet/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/contacts_extranet_emp/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/contacts_extranet_emp/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/tasks_extranet/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/tasks_extranet/index.php",
	),
	array(
		"CONDITION" => "#^/stssync/calendar_extranet/#",
		"RULE" => "",
		"ID" => "thurly:stssync.server",
		"PATH" => "/thurly/services/stssync/calendar_extranet/index.php",
	),
	array(
		"CONDITION" => "#^/onec/#",
		"RULE" => "",
		"ID" => "thurly:crm.1c.start",
		"PATH" => "/onec/index.php",
	),
	array(
		"CONDITION" => "#^/settings/configs/userconsent/#",
		"RULE" => "",
		"ID" => "thurly:intranet.userconsent",
		"PATH" => "/settings/configs/userconsent.php",
	),
	array(
		"CONDITION" => "#^/sites/#",
		"RULE" => "",
		"ID" => "thurly:landing.start",
		"PATH" => "/sites/index.php",
	),
	array(
		"CONDITION" => "#^\\/?\\/mobile/mobile_component\\/(.*)\\/.*#",
		"RULE" => "componentName=\$1",
		"ID" => "mobile_js_component",
		"PATH" => "/thurly/services/mobile/jscomponent.php",
	),
	array(
		"CONDITION" => "#^/marketing/letter/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/letter.php",
	),
	array(
		"CONDITION" => "#^/marketing/ads/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/ads.php",
	),
	array(
		"CONDITION" => "#^/marketing/segment/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/segment.php",
	),
	array(
		"CONDITION" => "#^/marketing/template/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/template.php",
	),
	array(
		"CONDITION" => "#^/marketing/blacklist/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/blacklist.php",
	),
	array(
		"CONDITION" => "#^/marketing/contact/#",
		"RULE" => "",
		"ID" => "",
		"PATH" => "/marketing/contact.php",
	),
);

?>