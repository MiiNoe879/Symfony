<?php
$pull_default_option = array(
	'path_to_listener' => "http://#DOMAIN#/thurly/sub/",
	'path_to_listener_secure' => "https://#DOMAIN#/thurly/sub/",
	'path_to_modern_listener' => "http://#DOMAIN#/thurly/sub/",
	'path_to_modern_listener_secure' => "https://#DOMAIN#/thurly/sub/",
	'path_to_mobile_listener' => "http://#DOMAIN#:8893/thurly/sub/",
	'path_to_mobile_listener_secure' => "https://#DOMAIN#:8894/thurly/sub/",
	'path_to_websocket' => "ws://#DOMAIN#/thurly/subws/",
	'path_to_websocket_secure' => "wss://#DOMAIN#/thurly/subws/",
	'path_to_publish' => 'http://127.0.0.1:8895/thurly/pub/',
	'nginx_version' => 2,
	'nginx_command_per_hit' => 100,
	'nginx' => 'N',
	'nginx_headers' => 'Y',
	'push' => 'N',
	'push_message_per_hit' => 100,
	'websocket' => 'Y',
	'signature_key' => '',
	'signature_algo' => 'sha1',
	'guest' => 'N',
);

if ($va = getenv('THURLY_VA_VER'))
{
	$pull_default_option['nginx'] = 'Y';
	$pull_default_option['nginx_version'] = 1;
	if (version_compare($va, '4.4', '>='))
		$pull_default_option['nginx_version'] = 2;
	if (version_compare($va, '7.1', '>='))
		$pull_default_option['nginx_version'] = 3;
}
?>
