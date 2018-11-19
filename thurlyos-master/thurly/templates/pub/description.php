<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

Thurly\Main\Localization\Loc::loadMessages(__FILE__);
$arTemplate = array(
	'NAME'        => GetMessage('TEMPLATE_NAME'),
	'DESCRIPTION' => ''
);
