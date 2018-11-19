<?php

/** @var $USER \CUser */
/** @var $APPLICATION \CMain */

define("PUBLIC_AJAX_MODE", true);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

\Thurly\Main\Loader::includeModule('faceid');

// set enabled flag
\Thurly\Main\Config\Option::set('faceid', 'user_index_enabled', 1);

// start indexing
\Thurly\Faceid\ProfilePhotoIndex::bindCustom(0);

// output indexing info
$stepperData = array('faceid' => array('Thurly\Faceid\ProfilePhotoIndex'));
echo \Thurly\Main\Update\Stepper::getHtml($stepperData, \Thurly\Main\Localization\Loc::getMessage("FACEID_TMS_START_INDEX_PHOTOS"));

CMain::FinalActions();
