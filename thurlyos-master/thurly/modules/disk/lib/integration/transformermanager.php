<?php

namespace Thurly\Disk\Integration;

use Thurly\Disk\File;
use Thurly\Disk\Uf\BlogPostCommentConnector;
use Thurly\Disk\Uf\BlogPostConnector;
use Thurly\Main\Application;
use Thurly\Transformer\DocumentTransformer;
use Thurly\Transformer\FileTransformer;
use Thurly\Transformer\InterfaceCallback;
use Thurly\Transformer\Command;
use Thurly\Main\Loader;
use Thurly\Transformer\VideoTransformer;

class TransformerManager implements InterfaceCallback
{
	const MODULE_ID = 'disk';
	const PATH = 'disk_preview';

	const COMMAND_STATUS_ERROR = 1000;

	/**
	 * Returns name of this class.
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * Function to process results after transformation.
	 *
	 * @param int $status Status of the command.
	 * @param string $command Name of the command.
	 * @param array $params Input parameters of the command.
	 * @param array $result Result of the command from controller
	 *      Here keys are identifiers to result information. If result is file it will be in 'files' array.
	 *      'files' - array of the files, where key is extension, and value is absolute path to the result file.
	 *
	 * This method returns true on success or string on error.
	 *
	 * @return bool|string
	 */
	public static function call($status, $command, $params, $result = array())
	{
		if(isset($params['fileId']) && $params['fileId'] > 0)
		{
			FileTransformer::clearInfoCache($params['fileId']);
		}
		if($status == Command::STATUS_ERROR && isset($params['id']))
		{
			Application::getInstance()->getTaggedCache()->clearByTag("disk_file_".$params['id']);
			BlogPostConnector::clearCacheByObjectId($params['id']);
			BlogPostCommentConnector::clearCacheByObjectId($params['id']);
			return true;
		}

		if(!isset($params['id']) || !isset($params['fileId']) || !isset($result['files']))
		{
			return 'wrong parameters';
		}

		if($status != Command::STATUS_UPLOAD)
		{
			return 'wrong command status';
		}

		$viewId = 0;
		$file = File::getById($params['id']);
		if(!$file)
		{
			return 'file '.$params['id'].' not found';
		}
		$commandInfo = FileTransformer::getTransformationInfoByFile($params['fileId']);
		$view = $file->getView();
		$previewFormat = $view->getPreviewExtension();
		$viewFormat = $view->getViewExtension();
		// there is no need to save preview and view for the file if it had been changed
		if($file->getFileId() == $params['fileId'])
		{
			if(isset($result['files'][$previewFormat]))
			{
				$previewId = self::saveFile($result['files'][$previewFormat], $view->getPreviewMimeType());
				if($previewId)
				{
					$file->changePreviewId($previewId);
				}
			}
			if(isset($result['files'][$viewFormat]))
			{
				$viewId = self::saveFile($result['files'][$viewFormat], $view->getMimeType());
				if($viewId)
				{
					$file->changeViewId($viewId);
				}
			}
		}
		// but we should save view for the version if it had not been joined
		if($view->isSaveForVersion())
		{
			if(isset($result['files'][$viewFormat]) && \Thurly\Main\IO\File::isFileExists($result['files'][$viewFormat]))
			{
				$versions = $file->getVersions(array('filter' => array('FILE_ID' => $params['fileId'])));
				$version = array_pop($versions);
				if($version)
				{
					if(!$viewId)
					{
						$viewId = self::saveFile($result['files'][$viewFormat], $view->getMimeType());
					}
					if($viewId)
					{
						$version->changeViewId($viewId);
					}
				}
			}
		}

		if($commandInfo && $commandInfo['id'] && Loader::includeModule('pull'))
		{
			\CPullWatch::AddToStack('VIDEOTRANSFORMATION'.$commandInfo['id'], array(
				'module_id' => 'transformer',
				'command' => 'refreshPlayer',
				'params' => array('id' => $commandInfo['id']),
			));
		}

		if(
			$view->isNeededLimitRightsOnTransformTime()
			&& Loader::includeModule('socialnetwork')
		)
		{
			$blogPostIDs = self::getBlogPostIds($file);
			foreach($blogPostIDs as $id)
			{
				\Thurly\Socialnetwork\ComponentHelper::setBlogPostLimitedViewStatus(array(
					'postId' => $id,
					'show' => true
				));
			}
		}

		Application::getInstance()->getTaggedCache()->clearByTag("disk_file_".$file->getId());
		BlogPostConnector::clearCacheByObjectId($file->getId());
		BlogPostCommentConnector::clearCacheByObjectId($file->getId());

		return true;
	}

	/**
	 * @param string $file Absolute path to the file.
	 * @param string $type Mime-type of the file.
	 * @return bool|int
	 */
	protected static function saveFile($file, $type)
	{
		$fileArray = \CFile::MakeFileArray($file, $type);
		$fileArray['MODULE_ID'] = self::MODULE_ID;
		$fileId = \CFile::SaveFile($fileArray, self::PATH);
		if($fileId)
		{
			return $fileId;
		}
		return false;
	}

	/**
	 * Fill parameters to call FileTransformer::transform().
	 *
	 * @param File $file
	 * @return bool
	 */
	public static function transformToView(File $file)
	{
		$view = $file->getView();

		if(!Loader::includeModule('transformer'))
		{
			return false;
		}

		$transformFormats = array($view->getPreviewExtension());
		$transformParams = array('id' => $file->getId(), 'fileId' => $file->getFileId());
		$viewExtension = $view->getViewExtension();
		$fileExtension = strtolower($file->getExtension());
		if($view::isAlwaysTransformToViewFormat())
		{
			$transformFormats[] = $viewExtension;
		}
		elseif($fileExtension != $viewExtension && !in_array($fileExtension, $view::getAlternativeExtensions()))
		{
			$transformFormats[] = $viewExtension;
		}

		$transformer = self::getTransformerByFormat($viewExtension);
		if($transformer)
		{
			$result = $transformer->transform((int)$file->getFileId(), $transformFormats, self::MODULE_ID, self::className(), $transformParams);
			return($result->isSuccess());
		}

		return false;
	}

	/**
	 * Fabric method to get transformer class by format.
	 *
	 * @param string $viewFormat Extension of the view.
	 * @return \Thurly\Transformer\FileTransformer|bool
	 */
	private static function getTransformerByFormat($viewFormat)
	{
		if($viewFormat == 'mp4')
		{
			return new VideoTransformer();
		}
		elseif($viewFormat == 'pdf')
		{
			return new DocumentTransformer();
		}

		return false;
	}

	/**
	 * Returns true if file had been sent to transform at least once.
	 *
	 * @param File $file
	 * @return bool
	 */
	public static function checkTransformationAttempts(File $file)
	{
		$info = FileTransformer::getTransformationInfoByFile((int)$file->getFileId());
		if($info)
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns array of BlogPost IDs to set limited rights
	 *
	 * @param File $file
	 * @return array
	 */
	public static function getBlogPostIds(File $file)
	{
		$blogPostIDs = array();

		$objects = $file->getAttachedObjects(array('filter' => array('=ENTITY_TYPE' => BlogPostConnector::className())));

		if(!empty($objects))
		{
			foreach($objects as $object)
			{
				$blogPostIDs[] = $object->getEntityId();
			}
		}

		return $blogPostIDs;
	}

	/**
	 * Returns array of SocNetLog IDs to set limited rights
	 *
	 * @param File $file
	 * @return array
	 */
	public static function getSocNetLogIds(File $file)
	{
		$logIds = array();

		if (Loader::includeModule('socialnetwork'))
		{
			$blogPostIDs = self::getBlogPostIds($file);
			if(!empty($blogPostIDs))
			{
				$entryInstance = new \Thurly\Socialnetwork\Livefeed\BlogPost;
				$socNetLogs = \Thurly\Socialnetwork\LogTable::getList(array(
					'filter' => array(
						'EVENT_ID' => $entryInstance->getEventId(),
						'SOURCE_ID' => $blogPostIDs,
					)
				))->fetchAll();

				foreach($socNetLogs as $socNetLog)
				{
					$logIds[] = $socNetLog['ID'];
				}
			}
		}

		return $logIds;
	}
}