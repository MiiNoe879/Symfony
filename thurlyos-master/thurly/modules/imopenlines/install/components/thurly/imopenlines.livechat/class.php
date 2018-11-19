<?php
use \Thurly\Main\Loader,
	\Thurly\Main\Localization\Loc;

class ImOpenLinesComponentLines extends CThurlyComponent
{
	private $configId = null;
	private $config = null;

	protected function checkModules()
	{
		if (!Loader::includeModule('im'))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_MODULE_IM_NOT_INSTALLED'));
			return false;
		}
		if (!Loader::includeModule('imopenlines'))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_MODULE_NOT_INSTALLED'));
			return false;
		}
		if (!Loader::includeModule('imconnector'))
		{
			\ShowError(Loc::getMessage('OL_COMPONENT_MODULE_IMCONNECTOR_NOT_INSTALLED'));
			return false;
		}
		return true;
	}

	private function prepareData()
	{
		$this->arResult['QUEUE'] = Array();

		$i=1;
		shuffle($this->config['QUEUE']);
		foreach ($this->config['QUEUE'] as $userId)
		{
			$user = \Thurly\Im\User::getInstance($userId);

			$userArray = Array(
				'ID' => $user->getId(),
				'NAME' => $user->getName(false),
				'LAST_NAME' => $user->getLastName(false),
				'PERSONAL_GENDER' => $user->getGender(),
				'PERSONAL_PHOTO' => $user->getAvatar()
			);
			if (function_exists('customImopenlinesOperatorNames') && !$user->isExtranet()) // Temporary hack :(
			{
				$userArray = customImopenlinesOperatorNames($this->configId, $userArray);
			}
			
			$this->arResult['QUEUE'][] = Array(
				"name" => htmlspecialcharsbx($userArray['NAME']),
				"avatar" => $userArray['PERSONAL_PHOTO'],
			);
			if ($i++ == 3)
			{
				break;
			}
		}

		$this->arResult['CONNECTORS'] = Array();
		$activeConnectors = \Thurly\ImConnector\Connector::infoConnectorsLine($this->configId);
		
		foreach ($activeConnectors as $code => $params)
		{
			if ($code == 'livechat' || empty($params['url']))
				continue;

			$this->arResult['CONNECTORS'][] = Array(
				'code' => $code,
				'link' => $params['url_im']? $params['url_im']: $params['url'],
				'title' => $params['name']? $params['name']:'',
			);
		}
		
		$liveChat = new \Thurly\ImOpenLines\LiveChat($this->config);
		if ($liveChat->openSession())
		{
			$this->arResult['CHAT'] = $liveChat->getChat();
		}

		return true;
	}


	private function showContextPage()
	{
		$liveChatManager = new \Thurly\ImOpenLines\LiveChatManager($this->configId);
		$config = $liveChatManager->get();

		$logoLang = LANGUAGE_ID;
		if (!in_array($logoLang, array('ru', 'ua', 'en')))
			$logoLang = \Thurly\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
		if (!in_array($logoLang, array('ru', 'ua', 'en')))
			$logoLang = 'en';

		$this->arResult['COPYRIGHT_LANG'] = $logoLang;
		$this->arResult['COPYRIGHT_REMOVED'] = $config['COPYRIGHT_REMOVED'];

		$this->arResult['CUSTOMIZATION']['BACKGROUND_IMAGE_PATH'] = htmlspecialcharsbx($config['BACKGROUND_IMAGE_LINK']);
		$this->arResult['CUSTOMIZATION']['TEMPLATE_ID'] = htmlspecialcharsbx($config['TEMPLATE_ID']);
		$this->arResult['CUSTOMIZATION']['CSS_ACTIVE'] = $config['CSS_ACTIVE'];
		$this->arResult['CUSTOMIZATION']['CSS_PATH'] = $config['CSS_ACTIVE'] == 'Y'? htmlspecialcharsbx($config['CSS_PATH']): '';
		$this->arResult['CUSTOMIZATION']['CSS_TEXT'] = $config['CSS_ACTIVE'] == 'Y'? htmlspecialcharsbx($config['CSS_TEXT']): '';

		$this->arResult['CUSTOMIZATION']['OG_CAPTION'] = $this->arResult['LINE_NAME'];
		$this->arResult['CUSTOMIZATION']['OG_DESCRIPTION'] = Loc::getMessage('OL_COMPONENT_LIVECHAT_DESCRIPTION');
		
		$siteName = '//' . \Thurly\Main\Context::getCurrent()->getServer()->getHttpHost();
		$siteNameHttp = 'http:' . $siteName;
		$siteNameHttps = 'https:' . $siteName;
		$this->arResult['CUSTOMIZATION']['OG_IMAGE'] = array(
			array(
				'PATH' => $siteNameHttp . $this->getPath() . '/templates/.default/images/rich_link_form_150_150.png',
				'PATH_HTTPS' => $siteNameHttps . $this->getPath() . '/templates/.default/images/rich_link_form_150_150.png',
				'TYPE' => 'image/png', 'WIDTH' => '150', 'HEIGHT' => '150'
			),array(
				'PATH' => $siteNameHttp . $this->getPath() . '/templates/.default/images/rich_link_form_150_100.png',
				'PATH_HTTPS' => $siteNameHttps . $this->getPath() . '/templates/.default/images/rich_link_form_150_100.png',
				'TYPE' => 'image/png', 'WIDTH' => '150', 'HEIGHT' => '100'
			),
		);
		
		if($config['BACKGROUND_IMAGE'])
		{
			if(in_array($this->getLanguageId(), array('ru', 'ua', 'by')))
			{
				$sizeList = array(
					array('width' => 150, 'height' => 100),
				);
			}
			else
			{
				$sizeList = array(
					array('width' => 150, 'height' => 150),
				);
			}
			foreach($sizeList as $size)
			{
				$image = CFile::ResizeImageGet(
					$config['BACKGROUND_IMAGE'],
					$size, BX_RESIZE_IMAGE_PROPORTIONAL, false
				);
				if(!$image['src'])
				{
					continue;
				}
				$backgroundImageSrcHttp = $image['src'];
				$backgroundImageSrcHttps = $backgroundImageSrcHttp;
				if(substr($backgroundImageSrcHttp, 0, 1) == '/')
				{
					$backgroundImageSrcHttp = $siteNameHttp . $backgroundImageSrcHttp;
					$backgroundImageSrcHttps = $siteNameHttps . $backgroundImageSrcHttps;
				}
				$this->arResult['CUSTOMIZATION']['OG_IMAGE'][] = array(
					'PATH' => $backgroundImageSrcHttp,
					'PATH_HTTPS' => $backgroundImageSrcHttps,
					'WIDTH' => $size['width'], 'HEIGHT' => $size['height']
				);
			}
		}
		
		$ogLang = strtoupper(\Thurly\Main\Context::getCurrent()->getLanguage());
		$ogLogo = $this->arResult['CUSTOMIZATION']['OG_IMAGE'];
		if(isset($this->arResult['CUSTOMIZATION']['OG_IMAGE_' . $ogLang]))
		{
			$ogLogo = $this->arResult['CUSTOMIZATION']['OG_IMAGE_' . $ogLang];
		}
		$this->arResult['CUSTOMIZATION']['OG_IMAGE_CURRENT'] = $ogLogo;
		if(Loader::includeModule('intranet'))
		{
			$this->arResult['CUSTOMIZATION']['REF_LINK'] = \CIntranetUtils::getB24Link('crm-form');
		}

		$this->includeComponentTemplate();
	}

	private function checkThirdPartyCookie()
	{
		$context = \Thurly\Main\Application::getInstance()->getContext();
		$request = $context->getRequest();

		if ($request->get('cookie') || $request->getCookieRaw('LIVECHAT_3RD'))
		{
			$this->arResult['ERROR'] = '';
			if ($request->getCookieRaw('LIVECHAT_3RD'))
			{
				$this->prepareData();
				if (\Thurly\Im\User::getInstance()->isConnector())
				{
					$this->showContextIframe();
				}
				else
				{
					$this->showContextError('INTRANET_USER');
				}
			}
			else
			{
				$this->showContextError('3RD_PARTY_COOKIE');
			}
		}
		else
		{
			setcookie('LIVECHAT_3RD', 'Y', time()+31536000, '/');
			$redirectUrl = \Thurly\ImOpenLines\Common::getServerAddress().$context->getServer()->get('REQUEST_URI').'&cookie=y';
			LocalRedirect($redirectUrl);
		}
	}

	private function showContextIframe()
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		if (Loader::includeModule('pull'))
		{
			\CPullOptions::OnEpilog();
		}

		$this->setTemplateName("iframe");
		$this->includeComponentTemplate();

		\CMain::finalActions();
		die();
	}

	private function showContextError($errorCode = 'ERROR')
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		$this->arResult['ERROR_CODE'] = $errorCode;

		$this->setTemplateName("iframe");
		$this->includeComponentTemplate();

		\CMain::finalActions();
		die();
	}

	public function executeComponent()
	{
		global $APPLICATION;

		$this->includeComponentLang('class.php');

		if (!$this->checkModules())
		{
			return false;
		}

		$this->configId = intval($this->arParams['CONFIG_ID']);
		if (!$this->configId)
			return false;

		$configManager = new \Thurly\ImOpenLines\Config();
		$this->config = $configManager->get($this->configId, true, false);
		$this->arResult['LINE_NAME'] = htmlspecialcharsbx($this->config['LINE_NAME']);

		if ($this->arParams['CONTEXT'] == 'IFRAME')
		{
			$this->checkThirdPartyCookie();
		}
		else
		{
			$this->showContextPage();
		}

		return true;
	}
};