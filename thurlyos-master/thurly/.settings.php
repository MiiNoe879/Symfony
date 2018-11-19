<?php
return array (
  'utf_mode' =>
  array (
    'value' => true,
    'readonly' => true,
  ),
  'cache' => array(
    'value' => array (
        'type' => 'memcache',
        'memcache' => array(
            'host' => 'unix:///tmp/memcached.sock',
            'port' => '0'
        ),
        'sid' => $_SERVER["DOCUMENT_ROOT"]."#01"
    ),
  ),
'pull_s1' => 'BEGIN GENERATED PUSH SETTINGS. DON\'T DELETE COMMENT!!!!',
  'pull' => Array(
    'value' =>  array(
        'path_to_listener' => "http://#DOMAIN#/thurly/sub/",
        'path_to_listener_secure' => "https://#DOMAIN#/thurly/sub/",
        'path_to_modern_listener' => "http://#DOMAIN#/thurly/sub/",
        'path_to_modern_listener_secure' => "https://#DOMAIN#/thurly/sub/",
        'path_to_mobile_listener' => "http://#DOMAIN#:8893/thurly/sub/",
        'path_to_mobile_listener_secure' => "https://#DOMAIN#:8894/thurly/sub/",
        'path_to_websocket' => "ws://#DOMAIN#/thurly/subws/",
        'path_to_websocket_secure' => "wss://#DOMAIN#/thurly/subws/",
        'path_to_publish' => 'http://127.0.0.1:8895/thurly/pub/',
        'nginx_version' => '3',
        'nginx_command_per_hit' => '100',
        'nginx' => 'Y',
        'nginx_headers' => 'N',
        'push' => 'Y',
        'websocket' => 'Y',
        'signature_key' => 'zMikSNXSsEidHucDkPgJjGHSB3EP0M5aUKAnLzz90R6YjIwZyluHidBiAtiu7m1Ya3S678dkhsz6eKMn1xMqeLLzQPb7fIG58F0uhoOXbzcyZ31axSlG89slcNC8duEC',
        'signature_algo' => 'sha1',
        'guest' => 'N',
    ),
  ),
'pull_e1' => 'END GENERATED PUSH SETTINGS. DON\'T DELETE COMMENT!!!!',

  'cache_flags' =>
  array (
    'value' =>
    array (
      'config_options' => 3600,
      'site_domain' => 3600,
    ),
    'readonly' => false,
  ),
  'cookies' =>
  array (
    'value' =>
    array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),
  'exception_handling' =>
  array (
    'value' =>
    array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => array (
          'settings' =>
          array (
            'file' => '/var/log/php/exceptions.log',
            'log_size' => 1000000,
        ),
      ),
    ),
    'readonly' => false,
  ),
  'connections' =>
  array (
    'value' =>
    array (
      'default' =>
      array (
        'className' => '\\Thurly\\Main\\DB\\MysqliConnection',
        'host' => 'localhost',
        'database' => 'sitemanager',
        'login'    => 'bitrix0',
        'password' => 'Z_P[w?7?So[AJUA[-Sr@',
        'options' => 2,
      ),
    ),
    'readonly' => true,
  )
);
