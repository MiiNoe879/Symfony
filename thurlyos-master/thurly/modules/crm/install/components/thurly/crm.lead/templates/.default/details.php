<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */

$APPLICATION->IncludeComponent(
	'thurly:crm.entity.details.frame',
	'',
	array(
		'ENTITY_TYPE_ID' => CCrmOwnerType::Lead,
		'ENTITY_ID' => $arResult['VARIABLES']['lead_id'],
		'ENABLE_TITLE_EDIT' => true

	)
);
