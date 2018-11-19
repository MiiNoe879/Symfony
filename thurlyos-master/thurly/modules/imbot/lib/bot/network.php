<?php
namespace Thurly\ImBot\Bot;

use Thurly\ImBot\Log;
use Thurly\Main\Config\Option;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Web\Json;

Loc::loadMessages(__FILE__);

class Network extends Base
{
	const BOT_CODE = "network";

	/* Important: sync with botcontroller/lib/bot/network.php:36 */
	protected static $fdcCodes = Array(
		'en' => "88c8eccd63f6ff5a59ba04e5b0f2012a",
		'by' => "a588e1a88baf601b9d0b0b33b1eefc2b",
		'ua' => "acb238d508bfbb0df68f200f21ae9b71",
		'kz' => "9020c408d2d43f407b68bbc88601dbe7",
		'ru' => "a588e1a88baf601b9d0b0b33b1eefc2b",
		'de' => "511dda9c421cdd21270a5f31d11f2fe5",
		'es' => "ae8cf733b2725127f755f8e75650a07a",
		'la' => "ae8cf733b2725127f755f8e75650a07a",
		'br' => "239e498332e63b5ee62b9e9fb0ff5a8d",
	);

	protected static $fdcLink = Array(
		'en' => Array('helpdesk' => 'http://helpdesk.thurlyos.com/', 'webinars' => 'https://www.thurlyos.com/support/webinars.php'),
		'by' => Array('helpdesk' => 'https://helpdesk.thurlyos.ru/', 'webinars' => 'https://webinars.thurlyos.ru/'),
		'ua' => Array('helpdesk' => 'https://helpdesk.thurlyos.ua/', 'webinars' => 'https://www.thurlyos.ua/support/webinars.php'),
		'kz' => Array('helpdesk' => 'https://helpdesk.thurlyos.ru/', 'webinars' => 'https://webinars.thurlyos.ru/'),
		'ru' => Array('helpdesk' => 'https://helpdesk.thurlyos.ru/', 'webinars' => 'https://webinars.thurlyos.ru/'),
		'de' => Array('helpdesk' => 'https://helpdesk.thurlyos.de/', 'webinars' => 'https://www.thurlyos.de/support/webinare.php'),
		'es' => Array('helpdesk' => 'https://helpdesk.thurlyos.es/', 'webinars' => 'https://www.thurlyos.es/support/webinars.php'),
		'la' => Array('helpdesk' => 'https://helpdesk.thurlyos.es/', 'webinars' => 'https://www.thurlyos.es/support/webinars.php'),
		'br' => Array('helpdesk' => 'http://helpdesk.thurlyos.com/', 'webinars' => 'https://www.thurlyos.com/support/webinars.php'),
	);

	public static function register(array $params = Array())
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		if (empty($params['CODE']))
			return false;

		$agentMode = isset($params['AGENT']) && $params['AGENT'] == 'Y';

		if (self::getNetworkBotId($params['CODE']))
			return $agentMode? "": self::getNetworkBotId($params['CODE']);

		$avatarData = self::uploadAvatar($params['LINE_AVATAR']);

		$botId = \Thurly\Im\Bot::register(Array(
			'APP_ID' => $params['CODE'],
			'CODE' => self::BOT_CODE.'_'.$params['CODE'],
			'MODULE_ID' => self::MODULE_ID,
			'TYPE' => \Thurly\Im\Bot::TYPE_NETWORK,
			'INSTALL_TYPE' => \Thurly\Im\Bot::INSTALL_TYPE_SILENT,
			'CLASS' => __CLASS__,
			'METHOD_MESSAGE_ADD' => 'onMessageAdd',
			'METHOD_BOT_DELETE' => 'onBotDelete',
			'TEXT_PRIVATE_WELCOME_MESSAGE' => isset($params['LINE_WELCOME_MESSAGE'])? $params['LINE_WELCOME_MESSAGE']: '',
			'PROPERTIES' => Array(
				'NAME' => $params['LINE_NAME'],
				'WORK_POSITION' => $params['LINE_DESC']? $params['LINE_DESC']: Loc::getMessage('IMBOT_NETWORK_BOT_WORK_POSITION'),
				'PERSONAL_PHOTO' => $avatarData,
			)
		));

		if ($botId)
		{
			self::setNetworkBotId($params['CODE'], $botId);

			$avatarId = \Thurly\Im\User::getInstance($botId)->getAvatarId();
			if ($avatarId > 0)
			{
				\Thurly\Im\Model\ExternalAvatarTable::add(Array(
					'LINK_MD5' => md5($params['LINE_AVATAR']),
					'AVATAR_ID' => $avatarId
				));
			}

			$sendParams = Array('CODE' => $params['CODE'], 'BOT_ID' => $botId);
			if (isset($params['OPTIONS']) && !empty($params['OPTIONS']))
			{
				$sendParams['OPTIONS'] = $params['OPTIONS'];
			}

			$http = new \Thurly\ImBot\Http(self::BOT_CODE);
			$result = $http->query('RegisterBot', $sendParams, true);
			if (isset($result['error']))
			{
				self::unRegister($params['CODE'], false);
				return false;
			}

			\Thurly\Im\Command::register(Array(
				'MODULE_ID' => self::MODULE_ID,
				'BOT_ID' => $botId,
				'COMMAND' => 'unregister',
				'HIDDEN' => 'Y',
				'CLASS' => __CLASS__,
				'METHOD_COMMAND_ADD' => 'onLocalCommandAdd'
			));
		}

		return $agentMode? "": $botId;
	}

	public static function unRegister($code = '', $serverRequest = true)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		if ($code == '')
		{
			$orm = \Thurly\Im\Model\BotTable::getList(Array(
				'filter' => Array(
					'=CLASS' => __CLASS__
				)
			));
			while ($row = $orm->fetch())
			{
				self::unRegister($row['code']);
			}

			return true;
		}

		$botId = self::getNetworkBotId($code);
		$result = \Thurly\Im\Bot::unRegister(Array('BOT_ID' => $botId));
		if ($result)
		{
			self::setNetworkBotId($code, 0);
			if ($serverRequest)
			{
				$http = new \Thurly\ImBot\Http(self::BOT_CODE);
				$result = $http->query(
					'UnRegisterBot',
					Array('CODE' => $code, 'BOT_ID' => $botId),
					true
				);
			}
		}

		return $result;
	}




	public static function onReceiveCommand($command, $params)
	{
		if($command == "operatorMessageAdd")
		{
			self::operatorMessageAdd($params['MESSAGE_ID'], Array(
				'BOT_ID' => $params['BOT_ID'],
				'BOT_CODE' => $params['BOT_CODE'],
				'DIALOG_ID' => $params['DIALOG_ID'],
				'MESSAGE' => $params['MESSAGE'],
				'FILES' => isset($params['FILES'])? $params['FILES']: '',
				'ATTACH' => isset($params['ATTACH'])? $params['ATTACH']: '',
				'KEYBOARD' => isset($params['KEYBOARD'])? $params['KEYBOARD']: '',
				'PARAMS' => isset($params['PARAMS'])? $params['PARAMS']: '',
				'USER' => isset($params['USER'])? $params['USER']: '',
				'LINE' => isset($params['LINE'])? $params['LINE']: ''
			));

			$result = Array('RESULT' => 'OK');
		}
		else if($command == "operatorMessageUpdate")
		{
			Log::write($params, 'NETWORK: operatorMessageUpdate');

			self::operatorMessageUpdate($params['MESSAGE_ID'], Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['DIALOG_ID'],
				'MESSAGE' => $params['MESSAGE'],
				'FILES' => isset($params['FILES'])? $params['FILES']: '',
				'ATTACH' => isset($params['ATTACH'])? $params['ATTACH']: '',
				'PARAMS' => isset($params['PARAMS'])? $params['PARAMS']: '',
				'CONNECTOR_MID' => $params['CONNECTOR_MID'],
			));
			$result = Array('RESULT' => 'OK');
		}
		else if($command == "operatorMessageDelete")
		{
			Log::write($params, 'NETWORK: operatorMessageDelete');

			self::operatorMessageDelete($params['MESSAGE_ID'], Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['DIALOG_ID'],
				'CONNECTOR_MID' => $params['CONNECTOR_MID'],
			));

			$result = Array('RESULT' => 'OK');
		}
		else if($command == "operatorStartWriting")
		{
			Log::write($params, 'NETWORK: operatorStartWriting');

			self::operatorStartWriting(Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['DIALOG_ID'],
				'USER' => isset($params['USER'])? $params['USER']: ''
			));

			$result = Array('RESULT' => 'OK');
		}
		else if($command == "operatorMessageReceived")
		{
			Log::write($params, 'NETWORK: operatorMessageReceived');

			self::operatorMessageReceived(Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['DIALOG_ID'],
				'MESSAGE_ID' => $params['MESSAGE_ID'],
				'CONNECTOR_MID' => $params['CONNECTOR_MID'],
				'SESSION_ID' => $params['SESSION_ID']
			));

			$result = Array('RESULT' => 'OK');
		}
		else
		{
			$result = new \Thurly\ImBot\Error(__METHOD__, 'UNKNOWN_COMMAND', 'Command is not found');
		}

		return $result;
	}




	private static function clientMessageAdd($messageId, $messageFields)
	{
		if ($messageFields['SYSTEM'] == 'Y')
			return false;

		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE || $messageFields['TO_USER_ID'] != $messageFields['BOT_ID'])
			return false;

		$bot = \Thurly\Im\Bot::getCache($messageFields['BOT_ID']);
		if (substr($bot['CODE'], 0, 7) != self::BOT_CODE)
			return false;

		$orm = \Thurly\Main\UserTable::getById($messageFields['FROM_USER_ID']);
		$user = $orm->fetch();

		$avatarUrl = '';
		if ($user['PERSONAL_PHOTO'])
		{
			$fileTmp = \CFile::ResizeImageGet(
				$user['PERSONAL_PHOTO'],
				array('width' => 300, 'height' => 300),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if ($fileTmp['src'])
			{
				$avatarUrl = substr($fileTmp['src'], 0, 4) == 'http'? $fileTmp['src']: \Thurly\ImBot\Http::getServerAddress().$fileTmp['src'];
			}
		}

		$files = Array();
		if (isset($messageFields['FILES']) && \Thurly\Main\Loader::includeModule('disk'))
		{
			foreach ($messageFields['FILES'] as $file)
			{
				$fileModel = \Thurly\Disk\File::loadById($file['id']);
				if (!$fileModel)
					continue;

				$extModel = $fileModel->addExternalLink(array(
					'CREATED_BY' => $messageFields['FROM_USER_ID'],
					'TYPE' => \Thurly\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
				));
				if (!$extModel)
					continue;

				$file['link'] = \Thurly\Disk\Driver::getInstance()->getUrlManager()->getShortUrlExternalLink(array(
					'hash' => $extModel->getHash(),
					'action' => 'default',
				), true);

				if (!$file['link'])
					continue;

				$files[] = array(
					'name' => $file['name'],
					'type' => $file['type'],
					'link' => $file['link'],
					'size' => $file['size']
				);
			}
		}

		$messageFields['MESSAGE'] = preg_replace("/\\[CHAT=[0-9]+\\](.*?)\\[\\/CHAT\\]/", "\\1",  $messageFields['MESSAGE']);
		$messageFields['MESSAGE'] = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1",  $messageFields['MESSAGE']);

		$portalTariff = 'box';
		if (\Thurly\Main\Loader::includeModule('thurlyos'))
		{
			$portalTariff = \CThurlyOS::getLicenseType();
		}

		$botMessageText = '';
		$CIMHistory = new \CIMHistory();
		if ($result = $CIMHistory->GetRelatedMessages($messageId, 1, 0, false, false))
		{
			foreach($result['message'] as $message)
			{
				if (isset($message['params']['IMOL_QUOTE_MSG']) && $message['params']['IMOL_QUOTE_MSG'] == 'Y')
				{
					$botMessageText = $message['text'];
				}
				break;
			}
		}
		if ($botMessageText)
		{
			$messageFields['MESSAGE'] = str_repeat("-", 54)."\n".$botMessageText."\n".str_repeat("-", 54)."\n".$messageFields['MESSAGE'];
		}

		\CIMMessageParam::Set($messageId, Array('SENDING' => 'Y', 'SENDING_TS' => time()));

		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$query = $http->query(
			'clientMessageAdd',
			Array(
				'BOT_ID' => $messageFields['BOT_ID'],
				'DIALOG_ID' => $messageFields['DIALOG_ID'],
				'MESSAGE_ID' => $messageId,
				'MESSAGE_TYPE' => $messageFields['MESSAGE_TYPE'],
				'MESSAGE_TEXT' => $messageFields['MESSAGE'],
				'FILES' => $files,
				'USER' => Array(
					'ID' => $user['ID'],
					'NAME' => $user['NAME'],
					'LAST_NAME' => $user['LAST_NAME'],
					'PERSONAL_GENDER' => $user['PERSONAL_GENDER'],
					'WORK_POSITION' =>  $user['WORK_POSITION'],
					'EMAIL' => $user['EMAIL'],
					'PERSONAL_PHOTO' => $avatarUrl,
					'TARIFF' => $portalTariff,
					'REGISTER' => $portalTariff != 'box'? \COption::GetOptionInt('main', '~controller_date_create', time()): ''
				),
			)
		);
		if (isset($query['error']))
		{
			self::$lastError = new \Thurly\ImBot\Error(__METHOD__, $query->error->code, $query->error->msg);

			$message = Loc::getMessage('IMBOT_NETWORK_ERROR_NOT_FOUND');
			if (self::getError()->code == 'BOT_NOT_FOUND')
			{
				$message = Loc::getMessage('IMBOT_NETWORK_ERROR_BOT_NOT_FOUND');
			}

			\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $messageFields['BOT_ID']), Array(
				'DIALOG_ID' => $messageFields['DIALOG_ID'],
				'MESSAGE' => $message,
				'SYSTEM' => 'Y'
			));

			\CIMMessageParam::Set($messageId, Array('IS_DELIVERED' => 'N', 'SENDING' => 'N', 'SENDING_TS' => 0));
		}
		\CIMMessageParam::SendPull($messageId, Array('IS_DELIVERED', 'SENDING', 'SENDING_TS'));

		return true;
	}

	private static function clientMessageUpdate($messageId, $messageFields)
	{
		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE || $messageFields['TO_USER_ID'] != $messageFields['BOT_ID'])
			return false;

		$bot = \Thurly\Im\Bot::getCache($messageFields['BOT_ID']);
		if (substr($bot['CODE'], 0, 7) != self::BOT_CODE)
			return false;

		$messageFields['MESSAGE'] = preg_replace("/\\[CHAT=[0-9]+\\](.*?)\\[\\/CHAT\\]/", "\\1",  $messageFields['MESSAGE']);
		$messageFields['MESSAGE'] = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1",  $messageFields['MESSAGE']);

		$botMessageText = '';
		$CIMHistory = new \CIMHistory();
		if ($result = $CIMHistory->GetRelatedMessages($messageId, 1, 0, false, false))
		{
			foreach($result['message'] as $message)
			{
				if (isset($message['params']['IMOL_QUOTE_MSG']) && $message['params']['IMOL_QUOTE_MSG'] == 'Y')
				{
					$botMessageText = $message['text'];
				}
				break;
			}
		}
		if ($botMessageText)
		{
			$messageFields['MESSAGE'] = str_repeat("-", 54)."\n".$botMessageText."\n".str_repeat("-", 54)."\n".$messageFields['MESSAGE'];
		}

		$orm = \Thurly\Main\UserTable::getById($messageFields['FROM_USER_ID']);
		$user = $orm->fetch();

		$avatarUrl = '';
		if ($user['PERSONAL_PHOTO'])
		{
			$fileTmp = \CFile::ResizeImageGet(
				$user['PERSONAL_PHOTO'],
				array('width' => 300, 'height' => 300),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if ($fileTmp['src'])
			{
				$avatarUrl = substr($fileTmp['src'], 0, 4) == 'http'? $fileTmp['src']: \Thurly\ImBot\Http::getServerAddress().$fileTmp['src'];
			}
		}


		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$http->query(
			'clientMessageUpdate',
			Array(
				'BOT_ID' => $messageFields['BOT_ID'],
				'DIALOG_ID' => $messageFields['DIALOG_ID'],
				'MESSAGE_ID' => $messageId,
				'CONNECTOR_MID' => $messageFields['PARAMS']['CONNECTOR_MID'][0],
				'MESSAGE_TEXT' => $messageFields['MESSAGE'],
				'USER' => Array(
					'ID' => $user['ID'],
					'NAME' => $user['NAME'],
					'LAST_NAME' => $user['LAST_NAME'],
					'PERSONAL_GENDER' => $user['PERSONAL_GENDER'],
					'WORK_POSITION' =>  $user['WORK_POSITION'],
					'EMAIL' => $user['EMAIL'],
					'PERSONAL_PHOTO' => $avatarUrl
				)
			)
		);

		return true;
	}

	private static function clientMessageDelete($messageId, $messageFields)
	{
		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE || $messageFields['TO_USER_ID'] != $messageFields['BOT_ID'])
			return false;

		$bot = \Thurly\Im\Bot::getCache($messageFields['BOT_ID']);
		if (substr($bot['CODE'], 0, 7) != self::BOT_CODE)
			return false;

		$messageFields['MESSAGE'] = preg_replace("/\\[CHAT=[0-9]+\\](.*?)\\[\\/CHAT\\]/", "\\1",  $messageFields['MESSAGE']);
		$messageFields['MESSAGE'] = preg_replace("/\\[USER=[0-9]+\\](.*?)\\[\\/USER\\]/", "\\1",  $messageFields['MESSAGE']);

		$orm = \Thurly\Main\UserTable::getById($messageFields['FROM_USER_ID']);
		$user = $orm->fetch();

		$avatarUrl = '';
		if ($user['PERSONAL_PHOTO'])
		{
			$fileTmp = \CFile::ResizeImageGet(
				$user['PERSONAL_PHOTO'],
				array('width' => 300, 'height' => 300),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if ($fileTmp['src'])
			{
				$avatarUrl = substr($fileTmp['src'], 0, 4) == 'http'? $fileTmp['src']: \Thurly\ImBot\Http::getServerAddress().$fileTmp['src'];
			}
		}


		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$http->query(
			'clientMessageDelete',
			Array(
				'BOT_ID' => $messageFields['BOT_ID'],
				'DIALOG_ID' => $messageFields['DIALOG_ID'],
				'MESSAGE_ID' => $messageId,
				'CONNECTOR_MID' => $messageFields['PARAMS']['CONNECTOR_MID'][0],
				'USER' => Array(
					'ID' => $user['ID'],
					'NAME' => $user['NAME'],
					'LAST_NAME' => $user['LAST_NAME'],
					'PERSONAL_GENDER' => $user['PERSONAL_GENDER'],
					'WORK_POSITION' =>  $user['WORK_POSITION'],
					'EMAIL' => $user['EMAIL'],
					'PERSONAL_PHOTO' => $avatarUrl
				)
			)
		);

		return true;
	}

	private static function clientStartWriting($params)
	{
		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$http->query(
			'clientStartWriting',
			Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['USER_ID'],
				'USER_ID' => $params['USER_ID'],
			),
			false
		);

		return true;
	}

	private static function clientSessionVote($params)
	{
		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$http->query(
			'clientSessionVote',
			Array(
				'BOT_ID' => $params['BOT_ID'],
				'DIALOG_ID' => $params['USER_ID'],
				'SESSION_ID' => $params['SESSION_ID'],
				'MESSAGE_ID' => $params['MESSAGE']['PARAMS']['CONNECTOR_MID'][0],
				'ACTION' => $params['ACTION'],
				'USER_ID' => $params['USER_ID'],
			),
			false
		);

		return true;
	}

	private static function clientMessageReceived($params)
	{
		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$query = $http->query(
			'clientMessageReceived',
			$params
		);
		if (isset($query->error))
		{
			return false;
		}

		return true;
	}



	private static function operatorMessageAdd($messageId, $messageFields)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		if (!empty($messageFields['BOT_CODE']))
		{
			$list = \Thurly\Im\Bot::getListCache();
			foreach ($list as $botId => $botData)
			{
				if ($botData['TYPE'] != \Thurly\Im\Bot::TYPE_NETWORK)
				{
					continue;
				}

				if ($messageFields['BOT_CODE'] == $botData['APP_ID'])
				{
					$messageFields['BOT_ID'] = intval($botData['BOT_ID']);
					break;
				}
			}
			if (intval($messageFields['BOT_ID']) <= 0)
			{
				return false;
			}
		}

		$attach = null;
		if (!empty($messageFields['ATTACH']))
		{
			$attach = \CIMMessageParamAttach::GetAttachByJson($messageFields['ATTACH']);
		}

		$keyboard = Array();
		if (!empty($messageFields['KEYBOARD']))
		{
			$keyboard = Array('BOT_ID' => $messageFields['BOT_ID']);
			if (!isset($messageFields['KEYBOARD']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $messageFields['KEYBOARD'];
			}
			else
			{
				$keyboard = $messageFields['KEYBOARD'];
			}
			$keyboard = \Thurly\Im\Bot\Keyboard::getKeyboardByJson($keyboard, Array(), Array('ENABLE_FUNCTIONS' => 'Y'));
		}

		if (!empty($messageFields['FILES']))
		{
			if (!$attach)
			{
				$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
			}
			foreach ($messageFields['FILES'] as $key => $value)
			{
				$attach->AddFiles(array(
					array(
						"NAME" => $value['name'],
						"LINK" => $value['link'],
						"SIZE" => $value['size'],
					)
				));
			}
		}

		$params = Array();
		if (!empty($messageFields['PARAMS']))
		{
			$params = $messageFields['PARAMS'];
		}

		$params['CONNECTOR_MID'] = Array($messageId);

		if (!empty($messageFields['USER']))
		{
			$params['USER_ID'] = $messageFields['USER']['ID'];
			$nameTemplateSite = \CSite::GetNameFormat(false);
			$userName = \CUser::FormatName($nameTemplateSite, $messageFields['USER'], true, false);
			if ($userName)
			{
				$params['NAME'] = $userName;
			}
			if (\Thurly\Main\Loader::includeModule('im'))
			{
				$userAvatar = \Thurly\Im\User::uploadAvatar($messageFields['USER']['PERSONAL_PHOTO']);
				if ($userAvatar)
				{
					$params['AVATAR'] = $userAvatar;
				}
			}
		}

		if (!empty($messageFields['LINE']))
		{
			$botData = \Thurly\Im\User::getInstance($messageFields['BOT_ID']);
			$updateFields = Array();
			if ($messageFields['LINE']['NAME'] != htmlspecialcharsback($botData->getName()))
			{
				$updateFields['NAME'] = $messageFields['LINE']['NAME'];
			}
			if ($messageFields['LINE']['DESC'] != htmlspecialcharsback($botData->getWorkPosition()))
			{
				$updateFields['WORK_POSITION'] = $messageFields['LINE']['DESC'];
			}

			$bot = \Thurly\Im\Bot::getCache($messageFields['BOT_ID']);
			if ($messageFields['LINE']['WELCOME_MESSAGE'] != $bot['TEXT_PRIVATE_WELCOME_MESSAGE'])
			{
				\Thurly\Im\Bot::update(Array('BOT_ID' => $messageFields['BOT_ID']), Array(
					'TEXT_PRIVATE_WELCOME_MESSAGE' => $messageFields['LINE']['WELCOME_MESSAGE']
				));
			}

			if (!empty($messageFields['LINE']['AVATAR']))
			{
				$userAvatar = \Thurly\Im\User::uploadAvatar($messageFields['LINE']['AVATAR']);
				if ($userAvatar && $botData->getAvatarId() != $userAvatar)
				{
					$updateFields['NAME'] = $messageFields['LINE']['NAME'];
					$updateFields['AVATAR'] = $userAvatar;

					$connection = \Thurly\Main\Application::getConnection();
					$connection->query("UPDATE b_user SET PERSONAL_PHOTO = ".intval($updateFields['AVATAR'])." WHERE ID = ".intval($messageFields['BOT_ID']));
				}
			}

			if (!empty($updateFields))
			{
				unset($updateFields['AVATAR']);

				global $USER;
				$USER->Update($messageFields['BOT_ID'], $updateFields);
			}
		}

		$messageFields['URL_PREVIEW'] = isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N'? 'N': 'Y';
		$connectorMid = \Thurly\Im\Bot::addMessage(Array('BOT_ID' => $messageFields['BOT_ID']), Array(
			'DIALOG_ID' => $messageFields['DIALOG_ID'],
			'MESSAGE' => $messageFields['MESSAGE'],
			'URL_PREVIEW' => $messageFields['URL_PREVIEW'],
			'ATTACH' => $attach,
			'KEYBOARD' => $keyboard,
			'PARAMS' => $params
		));

		self::clientMessageReceived(Array(
			'BOT_ID' => $messageFields['BOT_ID'],
			'DIALOG_ID' => $messageFields['DIALOG_ID'],
			'MESSAGE_ID' => $messageId,
			'CONNECTOR_MID' => $connectorMid,
		));

		return true;
	}

	private static function operatorMessageUpdate($messageId, $messageFields)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$messageParamData = \Thurly\Im\Model\MessageParamTable::getList(Array(
			'select' => Array('PARAM_VALUE'),
			'filter' => array('=MESSAGE_ID' => $messageId, '=PARAM_NAME' => 'CONNECTOR_MID')
		))->fetch();
		if (!$messageParamData || $messageParamData['PARAM_VALUE'] != $messageFields['CONNECTOR_MID'])
		{
			return false;
		}

		$attach = null;
		if (!empty($messageFields['ATTACH']))
		{
			$attach = \CIMMessageParamAttach::GetAttachByJson($messageFields['ATTACH']);
		}

		if (!empty($messageFields['FILES']))
		{
			if (!$attach)
			{
				$attach = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
			}
			foreach ($messageFields['FILES'] as $key => $value)
			{
				$attach->AddFiles(array(
					array(
						"NAME" => $value['name'],
						"LINK" => $value['link'],
						"SIZE" => $value['size'],
					)
				));
			}
		}

		$messageFields['URL_PREVIEW'] = isset($messageFields['URL_PREVIEW']) && $messageFields['URL_PREVIEW'] == 'N'? 'N': 'Y';

		\Thurly\Im\Bot::updateMessage(Array('BOT_ID' => $messageFields['BOT_ID']), Array(
			'MESSAGE_ID' => $messageId,
			'DIALOG_ID' => $messageFields['DIALOG_ID'],
			'MESSAGE' => $messageFields['MESSAGE'],
			'URL_PREVIEW' => $messageFields['URL_PREVIEW'],
			'ATTACH' => $attach,
			'SKIP_CONNECTOR' => 'Y',
			'EDIT_FLAG' => 'Y',
		));

		return true;
	}

	private static function operatorMessageDelete($messageId, $messageFields)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$messageParamData = \Thurly\Im\Model\MessageParamTable::getList(Array(
			'select' => Array('PARAM_VALUE'),
			'filter' => array('=MESSAGE_ID' => $messageId, '=PARAM_NAME' => 'CONNECTOR_MID')
		))->fetch();
		if (!$messageParamData || $messageParamData['PARAM_VALUE'] != $messageFields['CONNECTOR_MID'])
		{
			return false;
		}

		\Thurly\Im\Bot::deleteMessage(Array('BOT_ID' => $messageFields['BOT_ID']), $messageId);

		return true;
	}

	private static function operatorStartWriting($params)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$userName = '';
		if (!empty($params['USER']))
		{
			$params['USER_ID'] = $params['USER']['ID'];
			$nameTemplateSite = \CSite::GetNameFormat(false);
			$userName = \CUser::FormatName($nameTemplateSite, $params['USER'], true, false);
			if ($userName)
			{
				$params['NAME'] = $userName;
			}
		}

		\Thurly\Im\Bot::startWriting(Array('BOT_ID' => $params['BOT_ID']), $params['DIALOG_ID'], $userName);

		return true;
	}

	private static function operatorMessageReceived($params)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$messageData = \Thurly\Im\Model\MessageTable::getList(Array(
			'select' => Array('CHAT_ID'),
			'filter' => array('=ID' => $params['MESSAGE_ID'])
		))->fetch();
		if (!$messageData)
		{
			return false;
		}

		$chatId = \CIMMessage::GetChatId($params['BOT_ID'], $params['DIALOG_ID']);
		if ($messageData['CHAT_ID'] != $chatId)
		{
			return false;
		}

		$messageParamData = \Thurly\Im\Model\MessageParamTable::getList(Array(
			'select' => Array('PARAM_VALUE'),
			'filter' => array('=MESSAGE_ID' => $params['MESSAGE_ID'], '=PARAM_NAME' => 'SENDING')
		))->fetch();
		if (!$messageParamData || $messageParamData['PARAM_VALUE'] != 'Y')
		{
			return false;
		}

		\CIMMessageParam::Set($params['MESSAGE_ID'], Array(
			'CONNECTOR_MID' => $params['CONNECTOR_MID'],
			'SENDING' => 'N',
			'SENDING_TS' => 0,
			'IMOL_SID' => $params['SESSION_ID']
		));
		\CIMMessageParam::SendPull($params['MESSAGE_ID'], Array('CONNECTOR_MID', 'SENDING', 'SENDING_TS', 'IMOL_SID'));

		return true;
	}




	public static function onChatStart($dialogId, $joinFields)
	{
		return true;
	}

	public static function onMessageAdd($messageId, $messageFields)
	{
		return self::clientMessageAdd($messageId, $messageFields);
	}

	public static function onMessageUpdate($messageId, $messageFields)
	{
		return self::clientMessageUpdate($messageId, $messageFields);
	}

	public static function onMessageDelete($messageId, $messageFields)
	{
		return self::clientMessageDelete($messageId, $messageFields);
	}

	public static function onStartWriting($params)
	{
		return self::clientStartWriting($params);
	}

	public static function onSessionVote($params)
	{
		return self::clientSessionVote($params);
	}

	public static function onAnswerAdd($command, $params)
	{
		return self::onReceiveCommand($command, $params);
	}

	public static function onLocalCommandAdd($messageId, $messageFields)
	{
		if ($messageFields['SYSTEM'] == 'Y')
			return false;

		if ($messageFields['COMMAND_CONTEXT'] != 'TEXTAREA')
			return false;

		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
			return false;

		if ($messageFields['COMMAND'] != 'unregister')
			return false;

		global $GLOBALS;
		$grantAccess = \IsModuleInstalled('thurlyos')? $GLOBALS['USER']->CanDoOperation('thurlyos_config'): $GLOBALS["USER"]->IsAdmin();
		if (!$grantAccess)
			return false;

		$botData = \Thurly\Im\Bot::getCache($messageFields['TO_USER_ID']);
		self::unRegister($botData['APP_ID']);

		return true;
	}




	public static function getLangMessage($messageCode = '')
	{
		return Loc::getMessage($messageCode);
	}

	public static function uploadAvatar($avatarUrl = '')
	{
		if (!$avatarUrl)
			return '';

		if (!in_array(strtolower(\GetFileExtension($avatarUrl)), Array('png', 'jpg')))
			return '';

		$recordFile = \CFile::MakeFileArray($avatarUrl);
		if (!\CFile::IsImage($recordFile['name'], $recordFile['type']))
			return '';

		if (is_array($recordFile) && $recordFile['size'] && $recordFile['size'] > 0 && $recordFile['size'] < 1000000)
		{
			$recordFile = array_merge($recordFile, array('MODULE_ID' => 'imbot'));
		}
		else
		{
			$recordFile = '';
		}

		return $recordFile;
	}

	public static function join($code, $options = array())
	{
		if (!$code)
		{
			return false;
		}

		if ($result = \Thurly\ImBot\Bot\Network::getNetworkBotId($code))
		{
			return $result;
		}

		$result = self::search($code, true);
		if ($result)
		{
			if (!empty($options))
			{
				$result[0]['OPTIONS'] = $options;
			}
			$result = \Thurly\ImBot\Bot\Network::register($result[0]);
		}

		return $result;
	}

	public static function search($text, $register = false)
	{
		$text = trim($text);
		if (strlen($text) <= 3)
		{
			return false;
		}

		if (!$register && self::isFdcCode($text))
		{
			return false;
		}

		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$result = $http->query(
			'clientSearchLine',
			Array('TEXT' => $text),
			true
		);
		if (isset($result['error']))
		{
			self::$lastError = new \Thurly\ImBot\Error(__METHOD__, $result['error']['code'], $result['error']['msg']);
			return false;
		}

		return $result['result'];
	}

	public static function registerConnector($lineId, $fields = array())
	{
		$send['LINE_ID'] = intval($lineId);
		if ($send['LINE_ID'] <= 0)
		{
			return false;
		}
		$configManager = new \Thurly\ImOpenLines\Config();
		$config = $configManager->get($lineId);
		if (!$config)
		{
			return false;
		}

		$send['LINE_NAME'] = trim($fields['NAME']);
		if (strlen($send['LINE_NAME']) <= 0)
		{
			$send['LINE_NAME'] = $config['LINE_NAME'];
		}

		if (strlen($send['FIRST_MESSAGE']) <= 0)
		{
			$send['FIRST_MESSAGE'] = $config['WELCOME_MESSAGE_TEXT'];
		}

		$send['LINE_DESC'] = isset($fields['DESC'])? trim($fields['DESC']): '';
		$send['FIRST_MESSAGE'] = isset($fields['FIRST_MESSAGE'])? $fields['FIRST_MESSAGE']: '';

		$send['AVATAR'] = '';

		$fields['AVATAR'] = intval($fields['AVATAR']);
		if ($fields['AVATAR'])
		{
			$fileTmp = \CFile::ResizeImageGet(
				$fields['AVATAR'],
				array('width' => 300, 'height' => 300),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if ($fileTmp['src'])
			{
				$send['AVATAR'] = substr($fileTmp['src'], 0, 4) == 'http'? $fileTmp['src']: \Thurly\ImBot\Http::getServerAddress().$fileTmp['src'];
			}
		}

		$send['ACTIVE'] = isset($fields['ACTIVE']) && $fields['ACTIVE'] == 'N'? 'N': 'Y';
		$send['HIDDEN'] = isset($fields['HIDDEN']) && $fields['HIDDEN'] == 'Y'? 'Y': 'N';

		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$result = $http->query(
			'RegisterConnector',
			$send,
			true
		);
		if (isset($result['error']))
		{
			self::$lastError = new \Thurly\ImBot\Error(__METHOD__, $result['error']['code'], $result['error']['msg']);
			return false;
		}
		if ($result['result'])
		{
			$result = Array(
				'CODE' => $result['result'],
				'NAME' => $send['LINE_NAME'],
				'DESC' => $send['LINE_DESC'],
				'FIRST_MESSAGE' => $send['FIRST_MESSAGE'],
				'AVATAR' => $fields['AVATAR'],
				'ACTIVE' => $send['ACTIVE'],
				'HIDDEN' => $send['HIDDEN'],
			);
		}
		return $result;
	}

	public static function updateConnector($lineId, $fields)
	{
		$update['LINE_ID'] = intval($lineId);
		if ($update['LINE_ID'] <= 0)
		{
			return false;
		}

		if (isset($fields['NAME']))
		{
			$fields['NAME'] = trim($fields['NAME']);
			if (strlen($fields['NAME']) >= 3)
			{
				$update['FIELDS']['LINE_NAME'] = $fields['NAME'];
			}
			else
			{
				self::$lastError = new \Thurly\ImBot\Error(__METHOD__, 'NAME_LENGTH', 'Field NAME should be 3 or more characters');
				return false;
			}
		}

		if (isset($fields['DESC']))
		{
			$update['FIELDS']['LINE_DESC'] = trim($fields['DESC']);
		}

		if (isset($fields['FIRST_MESSAGE']))
		{
			$update['FIELDS']['FIRST_MESSAGE'] = trim($fields['FIRST_MESSAGE']);
		}

		if (isset($fields['AVATAR']))
		{
			$update['FIELDS']['AVATAR'] = '';

			$fields['AVATAR'] = intval($fields['AVATAR']);
			if ($fields['AVATAR'])
			{
				$fileTmp = \CFile::ResizeImageGet(
					$fields['AVATAR'],
					array('width' => 300, 'height' => 300),
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				if ($fileTmp['src'])
				{
					$update['FIELDS']['AVATAR'] = substr($fileTmp['src'], 0, 4) == 'http'? $fileTmp['src']: \Thurly\ImBot\Http::getServerAddress().$fileTmp['src'];
				}
			}
		}

		if (isset($fields['ACTIVE']))
		{
			$update['FIELDS']['ACTIVE'] = $fields['ACTIVE'] == 'N'? 'N': 'Y';
		}

		if (isset($fields['HIDDEN']))
		{
			$update['FIELDS']['HIDDEN'] = $fields['HIDDEN'] == 'Y'? 'Y': 'N';
		}

		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$result = $http->query(
			'UpdateConnector',
			$update,
			true
		);
		if (isset($result['error']))
		{
			self::$lastError = new \Thurly\ImBot\Error(__METHOD__, $result['error']['code'], $result['error']['msg']);
			return false;
		}

		return $result['result'];
	}

	public static function unRegisterConnector($lineId)
	{
		$update['LINE_ID'] = intval($lineId);
		if ($update['LINE_ID'] <= 0)
		{
			return false;
		}

		$http = new \Thurly\ImBot\Http(self::BOT_CODE);
		$result = $http->query(
			'UnRegisterConnector',
			Array('LINE_ID' => $lineId),
			true
		);
		if (isset($result['error']))
		{
			self::$lastError = new \Thurly\ImBot\Error(__METHOD__, $result['error']['code'], $result['error']['msg']);
			return false;
		}

		return $result['result'];
	}




	public static function setNetworkBotId($code, $id)
	{
		\Thurly\Main\Config\Option::set(self::MODULE_ID, self::BOT_CODE.'_'.$code."_bot_id", $id);

		return true;
	}

	public static function getNetworkBotId($code)
	{
		if (!$code)
			return false;

		return \Thurly\Main\Config\Option::get(self::MODULE_ID, self::BOT_CODE.'_'.$code."_bot_id", 0);
	}

	public static function getBotId()
	{
		return false;
	}

	public static function setBotId($id)
	{
		return false;
	}

	/*
	******************************************
	******************************************
	* ThurlyOS: Consultant of the first day
	******************************************
	******************************************
	*/
	private static function getLangForFdc()
	{
		if (\Thurly\Main\Loader::includeModule('thurlyos'))
		{
			$lang = 'en';
			$prefix = \CThurlyOS::getLicensePrefix();
			if (isset(self::$fdcCodes[$prefix]))
			{
				$lang = $prefix;
			}
		}
		else
		{
			$lang = 'ru';
		}
		return $lang;
	}

	public static function isFdcActive()
	{
		$lang = self::getLangForFdc();
		$result = Option::get(self::MODULE_ID, 'fdc_active_'.$lang);

		if ($result)
		{
			$distribution = Option::get(self::MODULE_ID, 'fdc_distribution');

			$result = ((hexdec(substr(md5(BX24_HOST_NAME), -2)) % $distribution) == 0);
		}

		return $result;
	}

	public static function isFdcCode($code)
	{
		return in_array($code, self::$fdcCodes);
	}

	public static function getFdcCode()
	{
		$lang = self::getLangForFdc();
		return isset(self::$fdcCodes[$lang])? self::$fdcCodes[$lang]: false;
	}

	public static function getFdcLink($type)
	{
		$lang = self::getLangForFdc();
		return isset(self::$fdcLink[$lang][$type])? self::$fdcLink[$lang][$type]: '';
	}

	public static function getFdcLifetime($seconds = true)
	{
		$lang = self::getLangForFdc();

		$lifetime = Option::get(self::MODULE_ID, 'fdc_lifetime_'.$lang);

		return intval($lifetime)*($seconds? 86400: 1);
	}

	public static function isPartnerFdc()
	{
		if (!IsModuleInstalled('thurlyos'))
			return false;

		$partnerOlCode = \COption::GetOptionString("thurlyos", "partner_ol", "");

		return (strlen($partnerOlCode) == 32);
	}

	public static function getPartnerId()
	{
		if (!IsModuleInstalled('thurlyos'))
			return false;

		$partnerId = \COption::GetOptionString("thurlyos", "partner_id", 0);

		return $partnerId > 0? $partnerId: false;
	}

	public static function addFdc($userId)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$joinCode = \COption::GetOptionString("thurlyos", "partner_ol", "");
		if (strlen($joinCode) == 32)
		{
			$joinOptions = Array(
				'TYPE' => 'PARTNER',
				'PARNER_NAME' => \COption::GetOptionString("thurlyos", "partner_name", "")
			);
		}
		else
		{
			$joinCode = self::getFdcCode();
			$joinOptions = Array();
		}

		$botId = self::join($joinCode, $joinOptions);
		if ($botId)
		{
			if (self::isFdcCode($joinCode))
			{
				$days = self::getFdcLifetime(false);
				if ($days == 30)
				{
					\CAgent::AddAgent('\\Thurly\\ImBot\\Bot\\Network::sendTextFdc('.$userId.');', "imbot", "N", 120, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+120, "FULL"));
				}
				\CAgent::AddAgent('\\Thurly\\ImBot\\Bot\\Network::removeFdc('.$userId.');', "imbot", "N", self::getFdcLifetime(), "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+self::getFdcLifetime(), "FULL"));
			}
			\CIMMessage::GetChatId($userId, $botId);
		}

		return "";
	}

	public static function sendTextFdc($userId, $text = '30-1')
	{
		global $pPERIOD;

		$fdcCode = self::getFdcCode();
		$botId = self::getNetworkBotId($fdcCode);
		if (!\Thurly\Main\Loader::includeModule('im') || $botId <= 0)
			return "";

		$botData = \Thurly\Im\Bot::getCache($botId);
		if ($botData['METHOD_MESSAGE_ADD'] == "fdcOnMessageAdd")
			return "";

		if ($text == '30-1')
		{
			if ($botData['COUNT_MESSAGE'] == 0)
			{
				\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
					'DIALOG_ID' => $userId,
					'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_1_2'),
					'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y')
				));

				$text = '30-2';
				$pPERIOD = 1*86400;
			}
			else
			{
				$text = '30-7';
				$pPERIOD = 7*86400;
			}
		}
		else if ($text == '30-2')
		{
			if ($botData['COUNT_MESSAGE'] == 0)
			{
				\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
					'DIALOG_ID' => $userId,
					'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_2'),
					'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y')
				));
				$text = '30-7';
				$pPERIOD = 6*86400;
			}
		}
		else if ($text == '30-7')
		{
			\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
				'DIALOG_ID' => $userId,
				'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_7'),
				'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y')
			));
			$text = '30-15';
			$pPERIOD = 7*86400;
		}
		else if ($text == '30-15')
		{
			\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
				'DIALOG_ID' => $userId,
				'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_15'),
				'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y')
			));
			$text = '30-23';
			$pPERIOD = 9*86400;
		}
		else if ($text == '30-23')
		{
			\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
				'DIALOG_ID' => $userId,
				'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_23'),
				'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y')
			));

			if (self::checkNeedRunMessageDay25())
			{
				$text = '30-25';
				$pPERIOD = 2*86400;
			}
			else
			{
				$text = '';
			}
		}
		else if ($text == '30-25' && self::checkNeedRunMessageDay25())
		{
			$keyboard = new \Thurly\Im\Bot\Keyboard($botId);
			$keyboard->addButton(Array(
				"TEXT" => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_25_BUTTON'),
				'LINK' => Option::get(self::MODULE_ID, 'fdc_day25_link'),
				"BG_COLOR" => "#4ba763",
				"TEXT_COLOR" => "#fff",
				"DISPLAY" => "LINE",
			));

			\Thurly\Im\Bot::addMessage(Array('BOT_ID' => $botId), Array(
				'DIALOG_ID' => $userId,
				'MESSAGE' => Loc::getMessage('IMBOT_NETWORK_FDC_30_MESSAGE_25'),
				'PARAMS' => Array('IMOL_QUOTE_MSG' => 'Y'),
				'KEYBOARD' => $keyboard
			));
			$text = '';
		}

		return $text? '\\Thurly\\ImBot\\Bot\\Network::sendTextFdc('.$userId.', "'.$text.'");': '';
	}

	public static function checkNeedRunMessageDay25()
	{
		if (!\Thurly\Main\Loader::includeModule('thurlyos'))
			return false;

		if (!Option::get(self::MODULE_ID, 'fdc_day25_link'))
			return false;

		if (self::getPartnerId())
			return false;

		return true;
	}

	public static function removeFdc($userId)
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return "";

		$fdcCode = self::getFdcCode();
		$botId = self::getNetworkBotId($fdcCode);
		if (!$botId)
			return "";

		$botData = \Thurly\Im\Bot::getCache($botId);
		if ($botData['METHOD_WELCOME_MESSAGE'] != 'fdcOnChatStart')
		{
			\Thurly\Im\Bot::update(Array('BOT_ID' => $botId), Array(
				'CLASS' => __CLASS__,
				'METHOD_BOT_DELETE' => '',
				'METHOD_MESSAGE_ADD' => 'fdcOnMessageAdd',
				'METHOD_WELCOME_MESSAGE' => 'fdcOnChatStart',
				'TEXT_PRIVATE_WELCOME_MESSAGE' => '',
			));
		}

		self::fdcOnChatStart($userId, Array(
			'CHAT_TYPE' => IM_MESSAGE_PRIVATE,
		));

		return "";
	}

	public static function fdcOnChatStart($dialogId, $joinFields)
	{
		if ($joinFields['CHAT_TYPE'] != IM_MESSAGE_PRIVATE)
			return false;

		$fdcCode = self::getFdcCode();
		$botId = self::getNetworkBotId($fdcCode);
		if (!$botId)
			return "";

		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$martaId = \Thurly\Imbot\Bot\Marta::getBotId();
		$supportId = \Thurly\Imbot\Bot\Support::getBotId();

		$userId = $dialogId;
		$userName = \Thurly\Im\User::getInstance($userId)->getName();

		$days = self::getFdcLifetime(false);
		$prefix = in_array($days, Array(1,7,30))? $days: 1;

		$langMessage = '';
		if ($supportId)
		{
			if (!\CUserOptions::GetOption('imbot', 'fdc_30_message', false, $userId))
			{
				\CUserOptions::SetOption('imbot', 'fdc_30_message', true, false, $userId);
				$langMessage = 'IMBOT_NETWORK_FDC_30_WITH_SUPPORT_BOT_2';
			}
			else
			{
				$langMessage = 'IMBOT_NETWORK_FDC_30_WITH_SUPPORT_BOT';
			}
		}
		else if (!$langMessage || $prefix == 30)
		{
			$langMessage = 'IMBOT_NETWORK_FDC_30_MESSAGE_30';
		}
		else
		{
			$langMessage = 'IMBOT_NETWORK_FDC_END_MESSAGE_'.$prefix;
		}
		$message = Loc::getMessage($langMessage, Array(
			'#USER_NAME#' => htmlspecialcharsback($userName),
			'#LINK_START_1#' => '[USER='.$martaId.']', '#LINK_END_1#' => '[/USER]',
			'#LINK_START_2#' => '[URL='.self::getFdcLink('helpdesk').']', '#LINK_END_2#' => '[/URL]',
			'#LINK_START_3#' => '[URL='.self::getFdcLink('webinars').']', '#LINK_END_3#' => '[/URL]',
			'#LINK_START_4#' => $supportId? '[USER='.$supportId.']': '',
			'#LINK_END_4#' => $supportId? '[/USER]': '',
			'#TARIFF_NAME#' => \Thurly\Main\Loader::includeModule('thurlyos')? \CThurlyOS::getLicenseName(): '',
		));
		if ($message)
		{
			\Thurly\Im\Bot::startWriting(Array('BOT_ID' => $botId), $dialogId);
			self::operatorMessageAdd(0, Array(
				'BOT_ID' => $botId,
				'DIALOG_ID' => $dialogId,
				'MESSAGE' => $message,
				'URL_PREVIEW' => 'N'
			));
		}

		return true;
	}

	public static function fdcOnMessageAdd($messageId, $messageFields)
	{
		if ($messageFields['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE)
			return false;

		$fdcCode = self::getFdcCode();
		$botId = self::getNetworkBotId($fdcCode);
		if (!$botId)
			return "";

		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$martaId = \Thurly\Imbot\Bot\Marta::getBotId();
		$supportId = \Thurly\Imbot\Bot\Support::getBotId();

		$userId = $messageFields['FROM_USER_ID'];
		$userName = \Thurly\Im\User::getInstance($userId)->getName();

		$days = self::getFdcLifetime(false);
		$prefix = in_array($days, Array(1,7,30))? $days: 1;

		if ($supportId)
		{
			if (!\CUserOptions::GetOption('imbot', 'fdc_30_message', false, $userId))
			{
				\CUserOptions::SetOption('imbot', 'fdc_30_message', true, false, $userId);
				$langMessage = 'IMBOT_NETWORK_FDC_30_WITH_SUPPORT_BOT_2';
			}
			else
			{
				$langMessage = 'IMBOT_NETWORK_FDC_30_WITH_SUPPORT_BOT';
			}
		}
		else if ($prefix == 30)
		{
			$langMessage = 'IMBOT_NETWORK_FDC_30_MESSAGE_30';
		}
		else
		{
			$langMessage = 'IMBOT_NETWORK_FDC_END_MESSAGE_'.$prefix;
		}
		$message = Loc::getMessage($langMessage, Array(
			'#USER_NAME#' => htmlspecialcharsback($userName),
			'#LINK_START_1#' => '[USER='.$martaId.']', '#LINK_END_1#' => '[/USER]',
			'#LINK_START_2#' => '[URL='.self::getFdcLink('helpdesk').']', '#LINK_END_2#' => '[/URL]',
			'#LINK_START_3#' => '[URL='.self::getFdcLink('webinars').']', '#LINK_END_3#' => '[/URL]',
			'#LINK_START_4#' => $supportId? '[USER='.$supportId.']': '',
			'#LINK_END_4#' => $supportId? '[/USER]': '',
			'#TARIFF_NAME#' => \Thurly\Main\Loader::includeModule('thurlyos')? \CThurlyOS::getLicenseName(): '',
		));
		if ($message)
		{
			\Thurly\Im\Bot::startWriting(Array('BOT_ID' => $botId), $messageFields['TO_USER_ID']);
			self::operatorMessageAdd(0, Array(
				'BOT_ID' => $botId,
				'DIALOG_ID' => $messageFields['DIALOG_ID'],
				'MESSAGE' => $message,
				'URL_PREVIEW' => 'N'
			));
		}

		return true;
	}

	public static function fdcOnAfterUserAuthorize($params)
	{
		$auth = \CHTTP::ParseAuthRequest();
		if (
			isset($auth["basic"]) && $auth["basic"]["username"] <> '' && $auth["basic"]["password"] <> ''
			&& strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'thurly') === false
		)
		{
			return true;
		}

		if (isset($params['update']) && $params['update'] === false)
			return true;

		if ($params['user_fields']['ID'] <= 0)
			return true;

		$params['user_fields']['ID'] = intval($params['user_fields']['ID']);

		if (isset($_SESSION['USER_LAST_CHECK_MARTA_'.$params['user_fields']['ID']]))
			return true;

		$martaCheck = \CUserOptions::GetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', 0, $params['user_fields']['ID']);
		if ($martaCheck > 0)
		{
			$_SESSION['USER_LAST_CHECK_MARTA_'.$params['user_fields']['ID']] = $martaCheck;
		}
		else
		{
			\CAgent::AddAgent('\\Thurly\\ImBot\\Bot\\Network::fdcAddWelcomeMessageAgent('.$params['user_fields']['ID'].');', "imbot", "N", 60, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+60, "FULL"));
		}

		return true;
	}

	public static function fdcAddWelcomeMessageAgent($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return "";

		if (\CUserOptions::GetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', 0, $userId) > 0)
			return "";

		if (!\Thurly\Main\Loader::includeModule('im'))
			return "";

		if (\Thurly\Im\User::getInstance($userId)->isExists() && \Thurly\Im\User::getInstance($userId)->isExtranet())
		{
			\CUserOptions::SetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', time(), false, $userId);
			$_SESSION['USER_LAST_CHECK_MARTA_'.$userId] = time();

			return "";
		}

		$userData = \Thurly\Main\UserTable::getById($userId)->fetch();
		if (in_array($userData['EXTERNAL_AUTH_ID'], Array('email', 'bot', 'network', 'imconnector')))
		{
			\CUserOptions::SetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', time(), false, $userId);
			$_SESSION['USER_LAST_CHECK_MARTA_'.$userId] = time();

			return "";
		}

		$language = null;
		$botData = \Thurly\Im\Bot::getCache(self::getBotId());
		if ($botData['LANG'])
		{
			$language = $botData['LANG'];
			Loc::loadLanguageFile(__FILE__, $language);
		}

		if (is_object($userData['TIMESTAMP_X']) && time() - $userData['TIMESTAMP_X']->getTimestamp() < 86400)
		{
			if (\Thurly\ImBot\Bot\Network::isPartnerFdc())
			{
				$fdcEnable = true;
			}
			else
			{
				$fdcEnable = \Thurly\ImBot\Bot\Network::isFdcActive();
			}
			if ($fdcEnable)
			{
				$generationDate = \COption::GetOptionInt('main', '~controller_date_create', 0);
				if (\Thurly\ImBot\Bot\Network::isPartnerFdc() || $generationDate == 0 || time() - $generationDate < 86400)
				{
					\Thurly\ImBot\Bot\Network::addFdc($userId);

					\CUserOptions::SetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', time(), false, $userId);
					$_SESSION['USER_LAST_CHECK_MARTA_'.$userId] = time();
					return "";
				}
			}
		}

		\CUserOptions::SetOption(self::MODULE_ID, self::BOT_CODE.'_welcome_message', time(), false, $userId);
		$_SESSION['USER_LAST_CHECK_MARTA_'.$userId] = time();

		return "";
	}
}