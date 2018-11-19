<?php
/**
 * S.N.M. router
 */

require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

\Thurly\Main\Data\AppCacheManifest::getInstance()->setExcludeImagePatterns(
	array(
		"fontawesome",
		"images/newpost",
		"images/files",
		"/crm",
		"images/im",
		"images/post",
		"images/notification",
		"images/messages",
		"images/lenta",
		"images/bizproc",
		"images/calendar",
		"images\/sprite.png", "images\/tri_")
);

$APPLICATION->IncludeComponent(
	'thurly:mobile.tasks.snmrouter',
	'.default', 
	array(
		'PREFIX_FOR_PATH_TO_SNM_ROUTER' => SITE_DIR.'mobile/tasks/snmrouter/',
		'DATE_TIME_FORMAT'              => 'j F Y G:i',
		'PATH_TO_SNM_ROUTER_AJAX'       => SITE_DIR.'mobile/?mobile_action=task_ajax',
		'PATH_TEMPLATE_TO_USER_PROFILE' => SITE_DIR.'mobile/users/?user_id=#USER_ID#',
		'AVATAR_SIZE'                   => array('width' => 58, 'height' => 58)
	), 
	false
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
