<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Thurly\Main\Loader;

if(!Loader::includeModule('rest'))
{
	return;
}

$request = \Thurly\Main\Context::getCurrent()->getRequest();

$clientId = $request['client_id'];
if(!$clientId)
{
	ShowError(\Thurly\Main\Localization\Loc::getMessage('REST_APP_NOT_FOUND'));
	return;
}

if($USER->IsAuthorized())
{
	if(isset($request['state']))
	{
		$state = $request['state'];
	}
	else
	{
		$state = '';
	}

	$authResult = \Thurly\Rest\OAuth\Auth::authorizeApplication($clientId, $USER->GetID(), $state);

	if($authResult['error'])
	{
		ShowError($authResult['error'].': '.$authResult['error_description']);
	}
	elseif($authResult['redirect_uri'])
	{
		$redirectUri = $authResult['redirect_uri'];

		unset($authResult['redirect_uri']);

		$fragment = '';
		if(array_key_exists('fragment', $authResult))
		{
			$fragment = $authResult['fragment'];
			unset($authResult['fragment']);
		}

		$authResult['server_domain'] = $authResult['domain'];
		$authResult['domain'] = $request->getHttpHost();

		$redirectUri .= (strpos($redirectUri, '?') !== false) ? '&' : '?';
		$redirectUri .= http_build_query($authResult);

		if(strlen($fragment) > 0)
		{
			$redirectUri .= '#'.$fragment;
		}

		LocalRedirect($redirectUri, true);
	}
	else
	{
		$arResult['OAUTH_PARAMS'] = $authResult;
		$this->includeComponentTemplate();
	}
}
else
{
	if(isset($request['client_id']))
	{
		$appInfo = \Thurly\Rest\AppTable::getByClientId($request['client_id']);
		if($appInfo && $appInfo['ACTIVE'] === \Thurly\Rest\AppTable::ACTIVE)
		{
			$APPLICATION->AuthForm(\Thurly\Main\Localization\Loc::getMessage('REST_NEED_AUTHORIZE_A', array(
				'#APP_ID#' => $appInfo['CODE']
			)));
			return;
		}
	}

	ShowError(\Thurly\Main\Localization\Loc::getMessage('REST_APP_NOT_FOUND'));
}
