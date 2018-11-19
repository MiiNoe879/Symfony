<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!\Thurly\Main\Loader::includeModule('disk'))
{
	return false;
}
class CDiskErrorPageComponent extends \Thurly\Disk\Internals\BaseComponent
{
	protected function processActionDefault()
	{
		Thurly\Main\Application::getInstance()
			->getContext()
			->getResponse()
			->setStatus('404 Not Found')
		;

		$this->includeComponentTemplate();
	}
}