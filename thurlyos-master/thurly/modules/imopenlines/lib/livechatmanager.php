<?php

namespace Thurly\ImOpenLines;

use Thurly\Main,
	Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class LiveChatManager
{
	private $error = null;
	private $id = null;

	const TEMPLATE_COLOR = 'color';
	const TEMPLATE_COLORLESS = 'colorless';

	const TYPE_WIDGET = 'widget';
	const TYPE_BUTTON = 'button';
	
	static $availableCount = null;

	public function __construct($configId)
	{
		$this->id = intval($configId);
		$this->config = false;
		$this->error = new Error(null, '', '');

		\Thurly\Main\Loader::includeModule("im");
	}

	public function add($fields = Array())
	{
		$configData = Model\LivechatTable::getById($this->id)->fetch();
		if ($configData)
		{
			$this->id = $configData['CONFIG_ID'];
			$this->config = false;
			
			return true;
		}
		
		$add['CONFIG_ID'] = $this->id;

		if (isset($fields['ENABLE_PUBLIC_LINK']))
		{
			$specifiedName = true;
			if (!isset($fields['URL_CODE_PUBLIC']))
			{
				$configManager = new \Thurly\ImOpenLines\Config();
				$config = $configManager->get($this->id);
				$fields['URL_CODE_PUBLIC'] = $config['LINE_NAME'];
				$specifiedName = false;
			}

			$add['URL_CODE_PUBLIC'] = self::prepareAlias($fields['URL_CODE_PUBLIC']);
			$add['URL_CODE_PUBLIC_ID'] = \Thurly\Im\Alias::add(Array(
				'ALIAS' => $add['URL_CODE_PUBLIC'],
				'ENTITY_TYPE' => \Thurly\Im\Alias::ENTITY_TYPE_OPEN_LINE,
				'ENTITY_ID' => $this->id
			));

			if (!$add['URL_CODE_PUBLIC_ID'])
			{
				if ($specifiedName)
				{
					$this->error = new Error(__METHOD__, 'CODE_ERROR', Loc::getMessage('IMOL_LCM_CODE_ERROR'));
					return false;
				}
				else
				{
					$result = \Thurly\Im\Alias::addUnique(Array(
						'ENTITY_TYPE' => \Thurly\Im\Alias::ENTITY_TYPE_OPEN_LINE,
						'ENTITY_ID' => $this->id
					));
					$add['URL_CODE_PUBLIC'] = $result['ALIAS'];
					$add['URL_CODE_PUBLIC_ID'] = $result['ID'];
				}
			}
		}

		$result = \Thurly\Im\Alias::addUnique(Array(
			'ENTITY_TYPE' => \Thurly\Im\Alias::ENTITY_TYPE_OPEN_LINE,
			'ENTITY_ID' => $this->id
		));
		$add['URL_CODE'] = $result['ALIAS'];
		$add['URL_CODE_ID'] = $result['ID'];

		if (isset($fields['TEMPLATE_ID']) && in_array($fields['TEMPLATE_ID'], Array(self::TEMPLATE_COLOR, self::TEMPLATE_COLORLESS)))
		{
			$add['TEMPLATE_ID'] = $fields['TEMPLATE_ID'];
		}
		if (isset($fields['BACKGROUND_IMAGE']))
		{
			$add['BACKGROUND_IMAGE'] = intval($fields['BACKGROUND_IMAGE']);
		}
		if (isset($fields['CSS_ACTIVE']))
		{
			$add['CSS_ACTIVE'] = $fields['CSS_ACTIVE'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CSS_PATH']))
		{
			$add['CSS_PATH'] = substr($fields['CSS_PATH'], 0, 255);
		}
		if (isset($fields['CSS_TEXT']))
		{
			$add['CSS_TEXT'] = $fields['CSS_TEXT'];
		}
		if (isset($fields['COPYRIGHT_REMOVED']) && Limit::canRemoveCopyright())
		{
			$add['COPYRIGHT_REMOVED'] = $fields['COPYRIGHT_REMOVED'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CACHE_WIDGET_ID']))
		{
			$add['CACHE_WIDGET_ID'] = intval($fields['CACHE_WIDGET_ID']);
		}
		if (isset($fields['CACHE_BUTTON_ID']))
		{
			$add['CACHE_BUTTON_ID'] = intval($fields['CACHE_BUTTON_ID']);
		}
		
		$result = Model\LivechatTable::add($add);
		if ($result->isSuccess())
		{
			$this->id = $result->getId();
			$this->config = false;
		}

		return $result->isSuccess();
	}

	public function update($fields)
	{
		$prevConfig = $this->get();

		$update = Array();
		if (isset($fields['URL_CODE_PUBLIC']))
		{
			$fields['URL_CODE_PUBLIC'] = trim($fields['URL_CODE_PUBLIC']);
			if (empty($fields['URL_CODE_PUBLIC']))
			{
				if ($prevConfig['URL_CODE_PUBLIC_ID'] > 0)
				{
					\Thurly\Im\Alias::delete($prevConfig['URL_CODE_PUBLIC_ID']);
				}
				$update['URL_CODE_PUBLIC'] = '';
				$update['URL_CODE_PUBLIC_ID'] = 0;
			}
			else
			{
				$fields['URL_CODE_PUBLIC'] = self::prepareAlias($fields['URL_CODE_PUBLIC']);
				if ($prevConfig['URL_CODE_PUBLIC_ID'] > 0)
				{
					if ($prevConfig['URL_CODE_PUBLIC'] != $fields['URL_CODE_PUBLIC'])
					{
						$result = \Thurly\Im\Alias::update($prevConfig['URL_CODE_PUBLIC_ID'], Array('ALIAS' => $fields['URL_CODE_PUBLIC']));
						if ($result)
						{
							$update['URL_CODE_PUBLIC'] = $fields['URL_CODE_PUBLIC'];
						}
					}
				}
				else
				{
					$fields['URL_CODE_PUBLIC_ID'] = \Thurly\Im\Alias::add(Array(
						'ALIAS' => $fields['URL_CODE_PUBLIC'],
						'ENTITY_TYPE' => \Thurly\Im\Alias::ENTITY_TYPE_OPEN_LINE,
						'ENTITY_ID' => $this->id
					));
					if ($fields['URL_CODE_PUBLIC_ID'])
					{
						$update['URL_CODE_PUBLIC'] = $fields['URL_CODE_PUBLIC'];
						$update['URL_CODE_PUBLIC_ID'] = $fields['URL_CODE_PUBLIC_ID'];
					}
				}
			}
		}

		if (isset($fields['TEMPLATE_ID']) && in_array($fields['TEMPLATE_ID'], Array(self::TEMPLATE_COLOR, self::TEMPLATE_COLORLESS)))
		{
			$update['TEMPLATE_ID'] = $fields['TEMPLATE_ID'];
		}
		if (isset($fields['CSS_ACTIVE']))
		{
			$update['CSS_ACTIVE'] = $fields['CSS_ACTIVE'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['BACKGROUND_IMAGE']))
		{
			$update['BACKGROUND_IMAGE'] = intval($fields['BACKGROUND_IMAGE']);
		}
		if (isset($fields['CSS_PATH']))
		{
			$update['CSS_PATH'] = substr($fields['CSS_PATH'], 0, 255);
		}
		if (isset($fields['CSS_TEXT']))
		{
			$update['CSS_TEXT'] = $fields['CSS_TEXT'];
		}
		if (isset($fields['COPYRIGHT_REMOVED']) && Limit::canRemoveCopyright())
		{
			$update['COPYRIGHT_REMOVED'] = $fields['COPYRIGHT_REMOVED'] == 'Y'? 'Y': 'N';
		}
		if (isset($fields['CACHE_WIDGET_ID']))
		{
			$update['CACHE_WIDGET_ID'] = intval($fields['CACHE_WIDGET_ID']);
		}
		if (isset($fields['CACHE_BUTTON_ID']))
		{
			$update['CACHE_BUTTON_ID'] = intval($fields['CACHE_BUTTON_ID']);
		}

		$result = Model\LivechatTable::update($this->id, $update);
		if ($result->isSuccess() && $this->config)
		{
			foreach ($update as $key => $value)
			{
				$this->config[$key] = $value;
			}
		}

		return $result->isSuccess();
	}

	public function delete()
	{
		$prevConfig = $this->get();

		if ($prevConfig['URL_CODE_PUBLIC_ID'])
		{
			\Thurly\Im\Alias::delete($prevConfig['URL_CODE_PUBLIC_ID']);
		}
		if ($prevConfig['URL_CODE_ID'])
		{
			\Thurly\Im\Alias::delete($prevConfig['URL_CODE_ID']);
		}

		if ($prevConfig['CACHE_WIDGET_ID'])
		{
			\CFile::Delete($prevConfig['CACHE_WIDGET_ID']);
		}
		if ($prevConfig['CACHE_BUTTON_ID'])
		{
			\CFile::Delete($prevConfig['CACHE_BUTTON_ID']);
		}
		
		Model\LivechatTable::delete($this->id);
		$this->config = false;

		return true;
	}

	public static function prepareAlias($alias)
	{
		if (!\Thurly\Main\Loader::includeModule("im"))
			return false;

		$alias = \CUtil::translit($alias, LANGUAGE_ID, array(
			"max_len"=>255,
			"safe_chars"=>".",
			"replace_space" => '-',
			"replace_other" => '-'
		));

		return \Thurly\Im\Alias::prepareAlias($alias);
	}

	public function checkAvailableName($alias)
	{
		if (!\Thurly\Main\Loader::includeModule("im"))
			return false;

		$alias = self::prepareAlias($alias);
		$orm = \Thurly\Im\Model\AliasTable::getList(Array(
			'filter' => Array(
				'=ALIAS' => $alias,
				'=ENTITY_TYPE' => \Thurly\Im\Alias::ENTITY_TYPE_OPEN_LINE,
				'!=ENTITY_ID' => $this->id
			)
		));

		return $orm->fetch()? false: true;
	}

	public static function canRemoveCopyright()
	{
		return \Thurly\Imopenlines\Limit::canRemoveCopyright();
	}

	public static function getFormatedUrl($alias = '')
	{
		return \Thurly\ImOpenLines\Common::getServerAddress().'/online/'.$alias;
	}

	public function get($configId = null)
	{
		if ($configId)
		{
			$this->id = intval($configId);
		}

		if ($this->id <= 0)
		{
			return false;
		}

		$orm = Model\LivechatTable::getById($this->id);
		$this->config = $orm->fetch();
		if (!$this->config)
			return false;

		$this->config['URL'] = self::getFormatedUrl($this->config['URL_CODE']);
		$this->config['URL_PUBLIC'] = self::getFormatedUrl($this->config['URL_CODE_PUBLIC']);
		$this->config['URL_SERVER'] = self::getFormatedUrl();
		$this->config['COPYRIGHT_REMOVED'] = self::canRemoveCopyright()? $this->config['COPYRIGHT_REMOVED']: "N";
		$this->config['CAN_REMOVE_COPYRIGHT'] = self::canRemoveCopyright()? 'Y':'N';
		$this->config['BACKGROUND_IMAGE_LINK'] = $this->config['BACKGROUND_IMAGE']? \CFile::GetPath($this->config['BACKGROUND_IMAGE']): "";

		return $this->config;
	}

	public function getPublicLink()
	{
		$orm = Model\LivechatTable::getList(array(
			'select' => Array('BACKGROUND_IMAGE', 'CONFIG_NAME' => 'CONFIG.LINE_NAME', 'URL_CODE_PUBLIC'),
			'filter' => Array('=CONFIG_ID' => $this->id)
		));
		$config = $orm->fetch();
		if (!$config)
			return false;

		$picture = '';
		if ($config['BACKGROUND_IMAGE'] > 0)
		{
			$image = \CFile::ResizeImageGet(
				$config['BACKGROUND_IMAGE'],
				array('width' => 300, 'height' => 200), BX_RESIZE_IMAGE_PROPORTIONAL, false
			);
			if($image['src'])
			{
				$picture = $image['src'];
			}
		}

		$result = Array(
			'ID' => $this->id,
			'NAME' => Loc::getMessage('IMOL_LCM_PUBLIC_NAME'),
			'LINE_NAME' => $config['CONFIG_NAME'],
			'PICTURE' => $picture,
			'URL' => self::getFormatedUrl($config['URL_CODE_PUBLIC']),
			'URL_IM' => self::getFormatedUrl($config['URL_CODE_PUBLIC'])
		);

		return $result;
	}

	public function getWidget($type = self::TYPE_WIDGET, $lang = null, $config = array(), $force = false)
	{
		$charset = SITE_CHARSET;
		$jsLink = self::getWidgetUrl($type, Array('LANG' => $lang, 'CONFIG' => $config, 'FORCE' => $force ? 'Y' : 'N'));
		if (!$jsLink)
			return false;

		$codeWidget =
			'<!-- ThurlyOS.LiveChat '.$type.' -->'."\n".
				'<script type="text/javascript">(function (d) {'.
					'var f = function () {'.
						'var n1 = document.getElementsByTagName("script")[0], r1=1*new Date(), s1 = document.createElement("script");'.' s1.type = "text/javascript"; s1.async = "true"; s1.charset = "'.$charset.'"; s1.src = "'.$jsLink.'?r="+r1;'.
						'n1.parentNode.insertBefore(s1, n1); '.
					'}; '.
					'if (typeof(BX)!="undefined" && typeof(BX.ready)!="undefined") {BX.ready(f)} '.
					'else if (typeof(jQuery)!="undefined") {jQuery(f)} '.
					($type == self::TYPE_WIDGET? 'else {d.addEventListener("DOMContentLoaded", f, false);}': 'else { setTimeout(f, 150); }').
				'})(document);</script>'."\n".
			'<!-- /ThurlyOS.LiveChat '.$type.' -->';

		return $codeWidget;
	}

	public static function updateCommonFiles($params = array())
	{
		// execute uploader if LiveChat exists
		$orm = \Thurly\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array('CNT'),
			'runtime' => array(
				new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			),
		));
		$row = $orm->fetch();
		if ($row['CNT'] <= 0)
		{
			return "";
		}

		// first upload
		$cdnFileUploaded = \COption::GetOptionInt('imopenlines', 'cdn_files_uploaded', 0);
		if (!$cdnFileUploaded)
		{
			$params['js'] = true;
			$params['css'] = true;
			$params['sprite'] = true;
			$params['logo'] = true;

			\COption::SetOptionInt('imopenlines', 'cdn_files_uploaded', 1);
		}

		$upload = Array();
		if (isset($params['js']))
		{
			$upload[] = Array(
				'folder' => 'script',
				'name' => 'livechat.js',
				'type' => 'text/javascript',
				'group' => 'js',
			);
		}
		if (isset($params['css']))
		{
			$upload[] = Array(
				'folder' => 'script',
				'name' => 'livechat.css',
				'type' => 'text/css',
				'replace' => Array(
					'./images/' => './../images/'
				),
				'group' => 'css',
			);
		}
		if (isset($params['sprite']))
		{
			$upload[] = Array(
				'folder' => 'images',
				'name' => 'images/sprite.svg',
				'type' => 'image/svg+xml',
				'group' => 'sprite',
			);
			$upload[] = Array(
				'folder' => 'images',
				'name' => 'images/sprite.png',
				'type' => 'image/png',
				'group' => 'sprite',
			);
		}
		if (isset($params['logo']))
		{
			$upload[] = Array(
				'folder' => 'images',
				'name' => 'images/logoua.png',
				'type' => 'image/png',
				'group' => 'logo',
			);
			$upload[] = Array(
				'folder' => 'images',
				'name' => 'images/logoru.png',
				'type' => 'image/png',
				'group' => 'logo',
			);
			$upload[] = Array(
				'folder' => 'images',
				'name' => 'images/logoen.png',
				'type' => 'image/png',
				'group' => 'logo',
			);
		}

		$error = Array();
		// upload files
		foreach ($upload as $file)
		{
			if (isset($file['replace']) && !empty($file['replace']))
			{
				$fileName = $file['name'];
				if ($slashPos = strrpos($fileName, '/'))
				{
					$fileName = substr($fileName, $slashPos+1);
				}
				$fileSource = \Thurly\Main\IO\File::getFileContents($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/imopenlines/install/js/imopenlines/'.$file['name']);

				$find = array();
				$replace = array();
				foreach ($file['replace'] as $key => $value)
				{
					$find[] = $key;
					$replace[] = $value;
				}
				$fileSource = str_replace($find, $replace, $fileSource);

				$cndFileArray = Array(
					"name" => $fileName,
					"type" => $file['type'],
					"MODULE_ID" => 'imopenlines',
					"content" => $fileSource
				);
			}
			else
			{
				$cndFileArray = \CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/imopenlines/install/js/imopenlines/'.$file['name']);
				$cndFileArray["type"] = $file['type'];
				$cndFileArray["MODULE_ID"] = "imopenlines";
			}

			// delete previous file
			$cdnFileId = \COption::GetOptionInt('imopenlines', 'cdn_files_'.str_replace(Array('/', '.'), '_', $file['name']), 0);
			if ($cdnFileId)
			{
				\CFile::Delete($cdnFileId);
			}

			// save file
			$id = \CFile::SaveFile($cndFileArray, 'livechat', false, false, $file['folder']);
			if ($id)
			{
				\COption::SetOptionInt('imopenlines', 'cdn_files_'.str_replace(Array('/', '.'), '_', $file['name']), $id);
			}
			else
			{
				$error[$file['group']] = true;
			}
		}

		// re-assemble files if upload is successful
		if (isset($params['cache']) && empty($error))
		{
			$orm = \Thurly\ImOpenLines\Model\LivechatTable::getList();
			while ($row = $orm->fetch())
			{
				// TODO send lang for widget
				$widget = new self($row['CONFIG_ID']);
				$widget->getWidgetSource();
			}
		}

		// if upload fail create agent
		if (!empty($error))
		{
			if (isset($params['cache']))
			{
				$error['cache'] = true;
			}
			$error['agent'] = true;
			\CAgent::AddAgent('\\Thurly\\ImOpenLines\\LiveChatManager::updateCommonFiles('.str_replace(Array("\n", "'", " ",",)"), Array("", '"', "", ")"), var_export($error, 1)).');', "imopenlines", "N", 60, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+60, "FULL"));
		}

		return "";
	}

	public static function getListForSelect()
	{
		$select = Array();
		$orm = \Thurly\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array(
				'CONFIG_ID', 'LINE_NAME' => 'CONFIG.LINE_NAME'
			)
		));
		while ($row = $orm->fetch())
		{
			$select[$row['CONFIG_ID']] = $row['LINE_NAME']? $row['LINE_NAME']: $row['CONFIG_ID'];
		}
		return $select;
	}

	private function getWidgetSource($params = array())
	{
		$config = $this->get();

		$params['LANG'] = isset($params['LANG'])? $params['LANG']: null;
		$params['CONFIG'] = is_array($params['CONFIG'])? $params['CONFIG']: Array();

		$charset = SITE_CHARSET;

		if (defined('IMOL_DIRECT_RESOURCES'))
		{
			$cssLink = \Thurly\ImOpenLines\Common::getServerAddress().'/thurly/js/imopenlines/livechat.css';
			$jsLink = \Thurly\ImOpenLines\Common::getServerAddress().'/thurly/js/imopenlines/livechat.js';
		}
		else
		{
			$cssLink = \Thurly\ImOpenLines\LiveChatManager::getCommonFileUrl('livechat.css');
			$jsLink = \Thurly\ImOpenLines\LiveChatManager::getCommonFileUrl('livechat.js');
		}
		if (!$cssLink || !$jsLink)
		{
			return false;
		}

		$localize = \Thurly\ImOpenLines\LiveChat::getLocalize($params['LANG'], false);

		$params['CONFIG']["thurlyos"] = \Thurly\ImOpenLines\Common::getServerAddress();
		$params['CONFIG']["code"] = $config['URL_CODE'];
		$params['CONFIG']["lang"] = $params['LANG'];
		$params['CONFIG']["copyright"] = isset($params['CONFIG']["copyright"])? $params['CONFIG']["copyright"]: true;
		$params['CONFIG']["copyrightUrl"] = \Thurly\ImOpenLines\Common::getThurlyUrlByLang($params['LANG']); 
		
		$codeWidget =
			'(function (d) {'.
				'var f = function (d) {'.
					'var n1 = document.getElementsByTagName("link")[0], s1 = document.createElement("link"), r1=1*new Date(); s1.type = "text/css"; s1.rel = "stylesheet";  s1.href = "'.$cssLink.'?r='.(defined('IMOL_DIRECT_RESOURCES')? '"+r1': time().'"').';'.
					'var n2 = document.getElementsByTagName("script")[0], s2 = document.createElement("script"); s2.type = "text/javascript"; s2.async = "true"; s2.charset = "'.$charset.'"; s2.src = "'.$jsLink.'?r='.(defined('IMOL_DIRECT_RESOURCES')? '"+r1': time().'"').';'.
					'if (n1) {n1.parentNode.insertBefore(s1, n1);} else { n2.parentNode.insertBefore(s1, n2); }'.
					'n2.parentNode.insertBefore(s2, n2);'.
				'};'.
				'if (typeof(BX)!="undefined" && typeof(BX.ready)!="undefined") {BX.ready(f)}'.
				'else if (typeof(jQuery)!="undefined") {jQuery(f)}'.
				'else { setTimeout(f, 150); }'.
			'})(document);'.
			'(window.BxLiveChatLoader = window.BxLiveChatLoader || []).push(function() {'.
				$localize.
				'BX.LiveChat.init('.Main\Web\Json::encode($params['CONFIG']).');'.
			'});';

		if ($config['CACHE_WIDGET_ID'])
		{
			\CFile::Delete($config['CACHE_WIDGET_ID']);
		}
		$cacheWidgetId = \CFile::SaveFile(Array(
			"name" => ToLower($config['URL_CODE']).".js",
			"type" => "text/javascript",
			"MODULE_ID" => 'imopenlines',
			"content" => $codeWidget
		), 'livechat', false, false, 'web');

		$params['CONFIG']["button"] = false;
		$codeWidget =
			'(function (d) {'.
				'var f = function (d) {'.
					'var n1 = document.getElementsByTagName("link")[0], s1 = document.createElement("link"), r1=1*new Date(); s1.type = "text/css"; s1.rel = "stylesheet";  s1.href = "'.$cssLink.'?r='.time().'";'.
					'var n2 = document.getElementsByTagName("script")[0], s2 = document.createElement("script"); s2.type = "text/javascript"; s2.async = "true"; s2.charset = "'.$charset.'"; s2.src = "'.$jsLink.'?r='.time().'";'.
					'if (n1) {n1.parentNode.insertBefore(s1, n1);} else { n2.parentNode.insertBefore(s1, n2); }'.
					'n2.parentNode.insertBefore(s2, n2);'.
				'};'.
				'if (typeof(BX)!="undefined" && typeof(BX.ready)!="undefined") {BX.ready(f)}'.
				'else if (typeof(jQuery)!="undefined") {jQuery(f)}'.
				'else { setTimeout(f, 150); }'.
			'})(document);'.
			'(window.BxLiveChatLoader = window.BxLiveChatLoader || []).push(function() {'.
				$localize.
				'BX.LiveChat.init('.Main\Web\Json::encode($params['CONFIG']).');'.
			'});';

		if ($config['CACHE_BUTTON_ID'])
		{
			\CFile::Delete($config['CACHE_BUTTON_ID']);
		}
		$cacheButtonId = \CFile::SaveFile(Array(
			"name" => ToLower($config['URL_CODE']).".js",
			"type" => "text/javascript",
			"MODULE_ID" => 'imopenlines',
			"content" => $codeWidget
		), 'livechat', false, false, 'button');

		$this->update(Array(
			'CACHE_BUTTON_ID' => $cacheButtonId,
			'CACHE_WIDGET_ID' => $cacheWidgetId
		));

		return true;
	}

	private function getWidgetUrl($type = self::TYPE_WIDGET, $params = array())
	{
		$config = $this->get();
		$cacheFileId = $type == self::TYPE_BUTTON? 'CACHE_BUTTON_ID': 'CACHE_WIDGET_ID';
		
		if (!$config[$cacheFileId] || $params['FORCE'] == 'Y')
		{
			if (!$this->getWidgetSource($params))
				return false;
		}

		$url = '';
		$config = $this->get();
		$result = \CFile::GetByID($config[$cacheFileId]);
		if ($file = $result->Fetch())
		{
			$url = $file['~src'];
			if (!$url)
			{
				$url = \Thurly\ImOpenLines\Common::getServerAddress().'/upload/'.$file['SUBDIR'].'/'.$file['FILE_NAME'];
			}
		}

		return $url;
	}

	private static function getCommonFileUrl($fileName)
	{
		$cdnFileId = \COption::GetOptionInt('imopenlines', 'cdn_files_'.str_replace(Array('/', '.'), '_', $fileName), 0);
		if (!$cdnFileId)
		{
			self::updateCommonFiles(Array('js' => true, 'css' => true, 'sprite' => true, 'logo' => true));
			$cdnFileId = \COption::GetOptionInt('imopenlines', 'cdn_files_'.str_replace(Array('/', '.'), '_', $fileName), 0);
		}

		if (!$cdnFileId)
		{
			return false;
		}

		$url = '';
		$result = \CFile::GetByID($cdnFileId);
		if ($file = $result->Fetch())
		{
			$url = $file['~src'];
			if (!$url)
			{
				$url = \Thurly\ImOpenLines\Common::getServerAddress().'/upload/'.$file['SUBDIR'].'/'.$file['FILE_NAME'];
			}
		}

		return $url;
	}
	
	public static function available()
	{
		if (!is_null(static::$availableCount))
		{
			return static::$availableCount > 0;
		}
		$orm = \Thurly\ImOpenLines\Model\LivechatTable::getList(Array(
			'select' => Array('CNT'),
			'runtime' => array(
				new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			),
		));
		$row = $orm->fetch();
		static::$availableCount = $row['CNT'];
		
		return ($row['CNT'] > 0);
	}
	
	public static function availableCount()
	{
		return is_null(static::$availableCount)? 0: static::$availableCount;
	}

	public function getError()
	{
		return $this->error;
	}
}