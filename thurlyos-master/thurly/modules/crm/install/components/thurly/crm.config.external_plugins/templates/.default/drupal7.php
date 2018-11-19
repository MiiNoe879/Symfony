<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$uri = new \Thurly\Main\Web\Uri($arResult['REQUEST']->getRequestUri());
$arParams['APP_URL'] = 'https://www.drupal.org/project/thurlyos/';

include 'detail.php';