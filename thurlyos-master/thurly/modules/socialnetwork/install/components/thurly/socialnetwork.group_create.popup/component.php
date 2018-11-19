<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2014 Thurly
 */

/**
 * Thurly vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @param array $arParams
 * @param array $arResult
 * @param CThurlyComponent $this
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["PATH_TO_GROUP_EDIT"] = (strlen($arParams["~PATH_TO_GROUP_EDIT"]) > 0 ? $arParams["~PATH_TO_GROUP_EDIT"] : "");
$arResult["GROUP_NAME"] = (strlen($arParams["~GROUP_NAME"]) > 0 ? $arParams["~GROUP_NAME"] : "");
$arResult["IS_PROJECT"] = (isset($arParams["IS_PROJECT"]) && $arParams["IS_PROJECT"] == 'Y' ? 'Y' : 'N');

$this->IncludeComponentTemplate();
?>