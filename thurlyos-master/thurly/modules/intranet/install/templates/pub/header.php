<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
\Thurly\Main\Localization\Loc::loadMessages(dirname(__FILE__)."/footer.php");

CUtil::initJSCore(array('ajax', 'popup'));

?><!DOCTYPE html>
<html>
<head>
<meta name="robots" content="noindex, nofollow, noarchive">
<?
$APPLICATION->showHead();
$APPLICATION->setAdditionalCSS("/thurly/templates/thurlyos/interface.css", true);
\Thurly\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/template_scripts.js", true);
?>
<title><? $APPLICATION->showTitle(); ?></title>
</head>

<body class="<?$APPLICATION->showProperty("BodyClass")?>">
<?
/*
This is commented to avoid Project Quality Control warning
$APPLICATION->ShowPanel();
*/
?>

<table class="main-wrapper">
	<tr>
		<td class="main-wrapper-content-cell">
			<div class="content-wrap">
				<div class="content">
					<h1 class="main-title">
					<? if (isModuleInstalled('thurlyos')) : ?>
						<? if ($clientLogo = COption::getOptionString('thurlyos', 'client_logo', '')) : ?>
						<img class="intranet-pub-title-user-logo" src="<?=CFile::getPath($clientLogo); ?>">
						<? else : ?>
						<span class="main-title-inner"><?=htmlspecialcharsbx(COption::getOptionString('thurlyos', 'site_title', '')); ?></span>
						<? if (COption::getOptionString('thurlyos', 'logo24show', 'Y') !== 'N') : ?><span class="title-num">Group</span><? endif; ?>
						<? endif; ?>
					<? else : ?>
						<? if ($logoID = COption::getOptionString('main', 'wizard_site_logo', '', SITE_ID)) : ?>
						<? $APPLICATION->includeComponent(
							'thurly:main.include', '',
							array('AREA_FILE_SHOW' => 'file', 'PATH' => SITE_DIR.'include/company_name.php')
						); ?>
						<? else : ?>
						<span class="main-title-inner"><?=htmlspecialcharsbx(COption::getOptionString('main', 'site_name', '')); ?></span>
						<span class="title-num">Group</span>
						<? endif; ?>
					<? endif; ?>
					</h1>
