<?php

namespace Thurly\Disk\View;

use Thurly\Disk\Configuration;
use Thurly\Disk\TypeFile;
use Thurly\Main\ArgumentNullException;

class Video extends Base
{
	// should be equal to \Thurly\Transformer\VideoTransformer::MAX_FILESIZE
	const TRANSFORM_VIDEO_MAX_LIMIT = 3221225472;

	const PLAYER_MIN_WIDTH = 400;
	const PLAYER_MIN_HEIGHT = 300;

	public function __construct($name, $fileId, $viewId = null, $previewId = null)
	{
		parent::__construct($name, $fileId, $viewId, $previewId);
		$preview = $this->getPreviewData();
		if(!empty($preview))
		{
			$sizes = $this->calculateSizes($preview,
				array('WIDTH' => $this->getJsViewerWidth(), 'HEIGHT' => $this->getJsViewerHeight()),
				array('WIDTH' => self::PLAYER_MIN_WIDTH, 'HEIGHT' => self::PLAYER_MIN_HEIGHT)
			);
			$this->jsViewerHeight = $sizes['HEIGHT'];
			$this->jsViewerWidth = $sizes['WIDTH'];
		}
	}

	/**
	 * Extension of the view.
	 *
	 * @return string
	 */
	public static function getViewExtension()
	{
		return 'mp4';
	}

	/**
	 * Is transformation allowed for this View.
	 *
	 * @return bool
	 */
	public static function isTransformationAllowedInOptions()
	{
		return Configuration::allowVideoTransformation();
	}

	/**
	 * Returns maximum allowed transformation file size.
	 *
	 * @return int
	 */
	public function getMaxSizeTransformation()
	{
		return Configuration::getMaxSizeForVideoTransformation();
	}

	/**
	 * Return type of viewer from core_viewer.js
	 *
	 * @return string|null
	 */
	public function getJsViewerType()
	{
		return 'ajax';
	}

	/**
	 * Return html code to view file.
	 *
	 * @param array $params
	 * @return string
	 */
	public function render($params = array())
	{
		if(empty($params) || !isset($params['PATH']) || empty($params['PATH']))
		{
			return '';
		}
		$params = $this->normalizePaths($params);
		$preview = $this->getPreviewData();
		if($params['IFRAME'] == 'Y')
		{
			$sizeType = 'fluid';
			$params['WIDTH'] = '';
			$params['HEIGHT'] = '';
		}
		else
		{
			$sizeType = 'adjust';
			if($preview)
			{
				$sizeType = 'fluid';
			}
			if(isset($params['SIZE_TYPE']) && !empty($params['SIZE_TYPE']))
			{
				$sizeType = $params['SIZE_TYPE'];
			}
			if(!empty($preview) && isset($params['WIDTH']) && isset($params['HEIGHT']))
			{
				$sizes = $this->calculateSizes($preview, $params);
				$params['WIDTH'] = $sizes['WIDTH'];
				$params['HEIGHT'] = $sizes['HEIGHT'];
			}
			if(!isset($params['WIDTH']))
			{
				$params['WIDTH'] = $this->getJsViewerWidth();
			}
			if(!isset($params['HEIGHT']))
			{
				$params['HEIGHT'] = $this->getJsViewerHeight();
			}
		}
		$autostart = 'Y';
		if(isset($params['AUTOSTART']) && $params['AUTOSTART'] == 'N')
		{
			$autostart = $params['AUTOSTART'];
		}
		if(isset($params['ID']))
		{
			$params['PLAYER_ID'] = $params['ID'];
		}
		ob_start();
		if($params['IS_MOBILE_APP'] === true)
		{
			$this->renderForMobileApp($params);
		}
		else
		{
			$this->renderForDesktop($params, $autostart, $sizeType);
		}
		return ob_get_clean();
	}

	/**
	 * Calculate sizes of popup with player.
	 * @param array $originalSizes
	 * @param array $maxSizes
	 * @param array $minSizes
	 * @return array
	 * @throws ArgumentNullException
	 */
	private function calculateSizes($originalSizes, $maxSizes, $minSizes = array())
	{
		if(!isset($originalSizes['WIDTH']) || !isset($originalSizes['HEIGHT']))
		{
			throw new ArgumentNullException('originalSizes');
		}
		if(!isset($maxSizes['WIDTH']) || !isset($maxSizes['HEIGHT']))
		{
			throw new ArgumentNullException('maxSizes');
		}
		if(!isset($minSizes['WIDTH']))
		{
			$minSizes['WIDTH'] = 0;
		}
		if(!isset($minSizes['HEIGHT']))
		{
			$minSizes['HEIGHT'] = 0;
		}
		if($originalSizes['WIDTH'] > $minSizes['WIDTH'] && $originalSizes['HEIGHT'] > $minSizes['HEIGHT'] &&
		 $originalSizes['WIDTH'] < $maxSizes['WIDTH'] && $originalSizes['HEIGHT'] < $maxSizes['HEIGHT'])
		{
			$newSizes = array(
				'WIDTH' => $originalSizes['WIDTH'],
				'HEIGHT' => $originalSizes['HEIGHT']
			);
			return $newSizes;
		}
		if($originalSizes['WIDTH'] < $minSizes['WIDTH'] || $originalSizes['HEIGHT'] < $minSizes['HEIGHT'])
		{
			$newSizes = array(
				'WIDTH' => $minSizes['WIDTH'],
				'HEIGHT' => $minSizes['HEIGHT']
			);
			$resultRelativeSize = $newSizes['WIDTH'] / $newSizes['HEIGHT'];
			$videoRelativeSize = $originalSizes['WIDTH'] / $originalSizes['HEIGHT'];
			if($resultRelativeSize > $videoRelativeSize)
			{
				$reduceRatio = $newSizes['WIDTH'] / $originalSizes['WIDTH'];
			}
			else
			{
				$reduceRatio = $newSizes['HEIGHT'] / $originalSizes['HEIGHT'];
			}
		}
		else
		{
			$newSizes = array(
				'WIDTH' => $maxSizes['WIDTH'],
				'HEIGHT' => $maxSizes['HEIGHT']
			);
			$resultRelativeSize = $newSizes['WIDTH'] / $newSizes['HEIGHT'];
			$videoRelativeSize = $originalSizes['WIDTH'] / $originalSizes['HEIGHT'];
			if($resultRelativeSize > $videoRelativeSize)
			{
				$reduceRatio = $newSizes['HEIGHT'] / $originalSizes['HEIGHT'];
			}
			else
			{
				$reduceRatio = $newSizes['WIDTH'] / $originalSizes['WIDTH'];
			}
		}
		$newSizes['WIDTH'] = floor($originalSizes['WIDTH'] * $reduceRatio);
		$newSizes['HEIGHT'] = floor($originalSizes['HEIGHT'] * $reduceRatio);
		return array('WIDTH' => $newSizes['WIDTH'], 'HEIGHT' => $newSizes['HEIGHT']);
	}

	/**
	 * Returns true if view can be rendered in some way.
	 *
	 * @return bool
	 */
	public function isHtmlAvailable()
	{
		if($this->getData() || $this->isTransformationAllowed())
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns html of the dummy view.
	 *
	 * @param array $params
	 * @return string
	 */
	public function renderTransformationInProcessMessage($params = array())
	{
		$dummyParams = array(
			'FILE' => $this->fileId,
			'TRANSFORM_URL' => $params['TRANSFORM_URL'],
			'TYPE' => 'video',
			'REFRESH_URL' => $params['REFRESH_URL'],
		);
		ob_start();
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'thurly:transformer.dummy',
			'',
			$dummyParams);
		return ob_get_clean();
	}

	/**
	 * Returns true if edit button should be hidden in js viewer.
	 *
	 * @return bool
	 */
	public function isJsViewerHideEditButton()
	{
		return true;
	}

	/**
	 * Get type attribute for bb-code in html-editor
	 *
	 * @return string
	 */
	public function getEditorTypeFile()
	{
		if($this->isHtmlAvailable())
		{
			return 'player';
		}

		return null;
	}

	/**
	 * Returns array of extensions that can be viewed.
	 *
	 * @return array
	 */
	public static function getViewableExtensions()
	{
		return array_merge(
			array(
				self::getViewExtension()
			),
			self::getAdditionalViewableExtensions(),
			self::getAlternativeExtensions()
		);
	}

	/**
	 * Returns array of alternative extensions, that has the same mime type as main extension
	 *
	 * @return array
	 */
	public static function getAlternativeExtensions()
	{
		return array('mp4v', 'mpg4');
	}

	/**
	 * @return array
	 */
	private static function getAdditionalViewableExtensions()
	{
		return array('flv', 'webm', 'ogv', 'mov');
	}

	/**
	 * Returns additional json array parameters for core_viewer.js
	 *
	 * @return array
	 */
	public function getJsViewerAdditionalJsonParams()
	{
		return array('wrapClassName' => 'bx-viewer-video');
	}

	/**
	 * Returns true if file should be transformed into view regardless of origin extension.
	 *
	 * @return bool
	 */
	public static function isAlwaysTransformToViewFormat()
	{
		return true;
	}

	/**
	 * Returns true if attached object with this file should have limited rights while transform in progress.
	 *
	 * @return bool
	 */
	public function isNeededLimitRightsOnTransformTime()
	{
		if($this->id > 0)
		{
			return false;
		}

		$mp4Formats = array_merge(
			array(
				self::getViewExtension()
			),
			self::getAlternativeExtensions()
		);
		if(in_array(strtolower($this->fileExtension), $mp4Formats))
		{
			return false;
		}

		if($this->isLastTransformationFailed())
		{
			return false;
		}

		return $this->isTransformationAllowed();
	}

	/**
	 * Check $params['PATH'] and fills $params['TRACKS'] from it with mime-types.
	 *
	 * @param array $params
	 * @return mixed
	 */
	protected function normalizePaths($params)
	{
		$mimeTypes = TypeFile::getMimeTypeExtensionList();
		if(is_array($params['PATH']))
		{
			foreach($params['PATH'] as $key => $source)
			{
				if($key == 0)
				{
					$type = $mimeTypes[$this->getExtension()];
				}
				else
				{
					$type = TypeFile::getMimeTypeByFilename($this->name);
				}
				$params['TRACKS'][] = array(
					'src' => $source,
					'type' => $type,
				);
			}
			$params['USE_PLAYLIST_AS_SOURCES'] = 'Y';
			$params['USE_PLAYLIST'] = 'Y';
			unset($params['PATH']);
		}
		else
		{
			$params['TYPE'] = $mimeTypes[$this->getExtension()];
		}
		return $params;
	}

	/**
	 * Include component to render player in mobile application.
	 *
	 * @param array $params
	 */
	protected function renderForMobileApp($params)
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'thurly:mobile.player',
			'',
			$params
		);
	}

	/**
	 * Include component to render player with custom skin in browser.
	 *
	 * @param array $params
	 * @param string $autostart
	 * @param string $sizeType
	 */
	protected function renderForDesktop($params, $autostart, $sizeType)
	{
		?><div class="disk-player-container<?if($sizeType == 'adjust')
		{
			?> player-adjust<?
		}
		?>"<?
		if($sizeType == 'fluid')
		{
			?> style="width: <?=$params['WIDTH'];?>px; height: <?=$params['HEIGHT'];?>px;"<?
		}
		if(isset($params['TRANSFORM_INFO_URL']) && !empty($params['TRANSFORM_INFO_URL']))
		{
			?> data-bx-transform-info-url="<?=$params['TRANSFORM_INFO_URL'];?>"<?
		}
		?>><?
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
		'thurly:player',
		'',
		array_merge($params, array(
			'SIZE_TYPE' => $sizeType,
			'PLAYER_TYPE' => 'videojs',
			'AUTOSTART' => $autostart,
			'SKIN_PATH' => '/thurly/js/disk/video/',
			'SKIN' => 'disk_player.css',
			'HIDE_ERRORS' => 'Y',
		)
		));
		?></div><?
	}

	/**
	 * Returns true if we should display message about transformation status.
	 *
	 * @return bool
	 */
	public function isShowTransformationInfo()
	{
		if(!self::isTransformationAllowedInOptions())
		{
			return false;
		}

		if($this->id > 0)
		{
			return false;
		}

		if($this->getSize() < self::TRANSFORM_VIDEO_MAX_LIMIT)
		{
			return true;
		}

		return false;
	}
}