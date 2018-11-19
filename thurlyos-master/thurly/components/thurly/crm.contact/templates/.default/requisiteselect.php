<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Thurly vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $component
 */

$APPLICATION->IncludeComponent(
	'thurly:crm.entity.requisite.select',
	'',
	array(
		'GUID' => 'contact_requisite_selector',
		'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
		'ENTITY_ID' => $arResult['VARIABLES']['contact_id']
	)
);