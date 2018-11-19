<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class OnecStartComponent extends CThurlyComponent
{
	/**
	 * Start Component
	 */
	public function executeComponent()
	{
		global $APPLICATION, $USER;

		$request = \Thurly\Main\Context::getCurrent()->getRequest();
		if($request->isPost() && check_thurly_sessid())
		{
			if(\Thurly\Main\Loader::includeModule('faceId'))
			{
				if($request['action'] == 'acceptAgreement' && \Thurly\FaceId\FaceCard::licenceIsRestricted() === false)
				{
					if(\Thurly\FaceId\FaceCard::agreementIsAccepted($USER->GetID()) === false)
					{
						\Thurly\Faceid\AgreementTable::add(array(
							'USER_ID' => $USER->GetID(),
							'NAME' => $USER->GetFullName(),
							'EMAIL' => $USER->GetEmail(),
							'DATE' => new \Thurly\Main\Type\DateTime,
							'IP_ADDRESS' => \Thurly\Main\Context::getCurrent()->getRequest()->getRemoteAddress()
						));
					}

					$APPLICATION->RestartBuffer();

					Header('Content-Type: application/json');
					echo \Thurly\Main\Web\Json::encode(array(
						'success' => true
					));
					\CMain::FinalActions();
					die();
				}
			}
		}

		$componentPage = '';
		$arDefaultUrlTemplates404 = array(
			'index' => '',
			'tracker' => 'tracker/',
			'report' => 'report/',
			'exchange' => 'exchange/'
		);

		$arDefaultVariableAliases404 = array();
		$arComponentVariables = array();
		$arVariables = array();
		$arVariableAliases = array();

		if ($this->arParams['SEF_MODE'] === 'Y')
		{
			$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams['SEF_URL_TEMPLATES']);
			$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams['VARIABLE_ALIASES']);
			$componentPage = CComponentEngine::ParseComponentPath($this->arParams['SEF_FOLDER'], $arUrlTemplates, $arVariables);
			
			if (!(is_string($componentPage) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage])))
			{
				$componentPage = 'index';
			}

			CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

			foreach ($arUrlTemplates as $url => $value)
			{
				$key = 'PATH_TO_ONEC_'.strtoupper($url);
				$arResult[$key] = isset($this->arParams[$key][0]) ? $this->arParams[$key] : $this->arParams['SEF_FOLDER'].$value;
			}
		}

		if(!$this->checkModuleByPage($componentPage, $error, $redirectUrl))
		{
			if($redirectUrl<>'')
			{
				LocalRedirect($this->arParams['SEF_FOLDER'].$redirectUrl);
			}
			else
			{
				ShowError(implode(', ', $error));
			}

			return;
		}

		$this->arResult =
			array_merge(
				array(
					'VARIABLES' => $arVariables,
					'ALIASES' => $this->arParams['SEF_MODE'] == 'Y' ? array(): $arVariableAliases
				),
				$this->arResult
			);

		switch ($componentPage)
		{
			case 'index':
				$this->arResult['RESTRICTED_LICENCE'] = \Thurly\FaceId\FaceCard::licenceIsRestricted();
				$this->arResult['LICENSE_ACCEPTED'] = $componentPage !== 'index' || \Thurly\FaceId\FaceCard::agreementIsAccepted($USER->GetID());
				$this->arResult['LICENSE_TEXT'] = \Thurly\Faceid\AgreementTable::getAgreementText(true);
				break;
		}
		switch ($componentPage)
		{
			case 'index':
			case 'tracker':
			case 'report':
				$this->arResult['APP'] = $this->getApplicationInfo();
				$this->arResult['APP_INACTIVE'] = $this->applicationIsInactive();
				break;
		}

		CJSCore::Init(array('popup', 'applayout'));

		$this->includeComponentTemplate($componentPage);
	}

	protected function applicationIsInactive()
	{
		$r = false;
		$appInfo = $this->getApplicationInfo();
		if(!$appInfo || $appInfo['ACTIVE'] === \Thurly\Rest\AppTable::INACTIVE)
		{
			$r = true;
		}
		return $r;
	}

	protected function getApplicationInfo()
	{
		return \Thurly\Rest\AppTable::getByClientId('thurly.1c');
	}

	protected function checkModuleByPage($page='', &$error, &$redirectUrl = '')
	{
		$error = array();
		
		switch ($page)
		{
			case 'index':
				if(\Thurly\Main\Loader::includeModule('faceId') && !\Thurly\FaceId\FaceId::isAvailable())
				{
					$error[] = 'faceCard';
					$redirectUrl = 'tracker/';
				}

				if(!\Thurly\Main\Loader::includeModule('faceId'))
				{
					$error[] = 'faceId';
					$redirectUrl = 'tracker/';
				}

				if(!\Thurly\Main\Loader::includeModule('rest'))
				{
					$error[] = 'rest';
					$redirectUrl = 'exchange/';
				}

				$r = count($error)<=0;				
				
				break;
			case 'tracker':
			case 'report':

				if(!\Thurly\Main\Loader::includeModule('rest'))
				{
					$error[] = 'rest';
					$redirectUrl = 'exchange/';
				}
					
				$r = count($error)<=0;
				break;
			case 'exchange':
				$r = true;
				break;
			default;
				$r = false;
		}
		return $r;
	}
}