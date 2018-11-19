<?php
use Thurly\Disk\Configuration;
use Thurly\Disk\Driver;
use Thurly\Disk\ExternalLink;
use Thurly\Disk\File;
use Thurly\Disk\Folder;
use Thurly\Disk\Internals\Error\Error;
use Thurly\Disk\Internals\ExternalLinkTable;
use Thurly\Disk\Internals\ObjectTable;
use Thurly\Disk\Version;
use Thurly\Main\EventResult;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Loader;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define("NOT_CHECK_PERMISSIONS", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID'])? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('disk') || !\Thurly\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
{
	return;
}

Loc::loadMessages(__FILE__);

class DiskFileViewAjaxController extends \Thurly\Disk\Internals\Controller
{
	const ERROR_COULD_NOT_FIND_OBJECT          = 'DISK_FLAC_22001';
	const ERROR_COULD_NOT_CREATE_FIND_EXT_LINK = 'DISK_FLAC_22004';
	const ERROR_COULD_NOT_FIND_VERSION         = 'DISK_FLAC_22005';
	const ERROR_COULD_NOT_UPDATE_FILE          = 'DISK_FLAC_22006';

	protected function listActions()
	{
		return array(
			'disableExternalLink' => array(
				'method' => array('POST'),
			),
			'getExternalLink' => array(
				'method' => array('POST'),
			),
			'generateExternalLink' => array(
				'method' => array('POST'),
			),
			'restoreFromVersion' => array(
				'method' => array('POST'),
			),
			'deleteVersion' => array(
				'method' => array('POST'),
			),
			'deleteBizProc' => array(
				'method' => array('POST'),
			),
			'stopBizProc' => array(
				'method' => array('POST'),
			),
			'saveUserField' => array(
				'method' => array('POST'),
			),
		);
	}

	private function getFileAndExternalLink()
	{
		if(!$this->checkRequiredPostParams(array('objectId')))
		{
			$this->sendJsonErrorResponse();
		}

		/** @var File $file */
		$file = File::loadById((int)$this->request->getPost('objectId'), array('STORAGE'));
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}

		$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
		if(!$file->canRead($securityContext))
		{
			$this->sendJsonAccessDeniedResponse();
		}
		$extLinks = $file->getExternalLinks(array(
			'filter' => array(
				'OBJECT_ID' => $file->getId(),
				'CREATED_BY' => $this->getUser()->getId(),
				'TYPE' => ExternalLinkTable::TYPE_MANUAL,
				'=IS_EXPIRED' => false,
			),
			'limit' => 1,
		));

		return array($file, array_pop($extLinks));
	}

	protected function processActionDisableExternalLink()
	{
		/** @var File $file */
		/** @var ExternalLink $extLink */
		list($file, $extLink) = $this->getFileAndExternalLink();
		if(!$extLink || $extLink->delete())
		{
			$this->sendJsonSuccessResponse();
		}
		$this->sendJsonErrorResponse();
	}

	protected function onBeforeActionGetExternalLink()
	{
		if(!Configuration::isEnabledExternalLink())
		{
			return new EventResult(EventResult::ERROR);
		}

		return new EventResult(EventResult::SUCCESS);
	}

	protected function onBeforeActionGenerateExternalLink()
	{
		if(!Configuration::isEnabledExternalLink())
		{
			return new EventResult(EventResult::ERROR);
		}

		return new EventResult(EventResult::SUCCESS);
	}

	protected function processActionGetExternalLink()
	{
		/** @var File $file */
		/** @var ExternalLink $extLink */
		list($file, $extLink) = $this->getFileAndExternalLink();

		if(!$extLink)
		{
			$this->sendJsonSuccessResponse(array(
				'hash' => null,
				'link' => null,
			));
		}
		$this->sendJsonSuccessResponse(array(
			'hash' => $extLink->getHash(),
			'link' => Driver::getInstance()->getUrlManager()->getShortUrlExternalLink(array(
				'hash' => $extLink->getHash(),
				'action' => 'default',
			), true),
		));
	}

	protected function processActionGenerateExternalLink()
	{
		/** @var File $file */
		list($file, $extLink) = $this->getFileAndExternalLink();
		if(!$extLink)
		{
			$extLink = $file->addExternalLink(array(
				'CREATED_BY' => $this->getUser()->getId(),
				'TYPE' => ExternalLinkTable::TYPE_MANUAL,
			));
		}
		if(!$extLink)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_CREATE_FIND_EXT_LINK'), self::ERROR_COULD_NOT_CREATE_FIND_EXT_LINK));
			$this->errorCollection->add($file->getErrors());
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse(array(
			'hash' => $extLink->getHash(),
			'link' => Driver::getInstance()->getUrlManager()->getShortUrlExternalLink(array(
				'hash' => $extLink->getHash(),
				'action' => 'default',
			), true),
		));
	}

	public function processActionRestoreFromVersion($objectId, $versionId)
	{
		/** @var File $file */
		$file = File::loadById((int)$objectId, array('STORAGE'));
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}

		/** @var Version $version */
		$version = $file->getVersion($versionId);
		if(!$version)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_VERSION'), self::ERROR_COULD_NOT_FIND_VERSION));
			$this->sendJsonErrorResponse();
		}

		$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
		if(!$file->canRestore($securityContext))
		{
			$this->sendJsonAccessDeniedResponse();
		}

		if(!$file->restoreFromVersion($version, $this->getUser()->getId()))
		{
			$this->errorCollection->add($file->getErrors());
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse();
	}

	public function processActionDeleteVersion($objectId, $versionId)
	{
		/** @var File $file */
		$file = File::loadById((int)$objectId, array('STORAGE'));
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}

		/** @var Version $version */
		$version = $file->getVersion($versionId);
		if(!$version)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_VERSION'), self::ERROR_COULD_NOT_FIND_VERSION));
			$this->sendJsonErrorResponse();
		}

		$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
		if(!$file->canDelete($securityContext) || !$file->canRestore($securityContext))
		{
			$this->sendJsonAccessDeniedResponse();
		}

		if(!$version->delete($this->getUser()->getId()))
		{
			$this->errorCollection->add($version->getErrors());
			$this->sendJsonErrorResponse();
		}

		$this->sendJsonSuccessResponse();
	}

	public function processActionDeleteBizProc($fileId, $idBizProc)
	{
		if (!\Thurly\Disk\Integration\BizProcManager::isAvailable())
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_BIZPROC_LOAD')));
			$this->sendJsonErrorResponse();
		}

		/** @var File $file */
		$file = File::loadById((int)$fileId);
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}

		$documentData = array(
			'DISK' => array(
				'DOCUMENT_ID' => \Thurly\Disk\BizProcDocument::getDocumentComplexId($fileId),
			),
			'WEBDAV' => array(
				'DOCUMENT_ID' => \Thurly\Disk\BizProcDocumentCompatible::getDocumentComplexId($fileId),
			),
		);

		$webdavFileId = $file->getXmlId();
		if(!empty($webdavFileId))
		{
			$documentData['OLD_FILE_COMMON'] = array(
				'DOCUMENT_ID' => array('webdav', 'CIBlockDocumentWebdav', $webdavFileId),
			);
			$documentData['OLD_FILE_GROUP'] = array(
				'DOCUMENT_ID' => array('webdav', 'CIBlockDocumentWebdavSocnet', $webdavFileId),
			);
		}

		$workflowId = $idBizProc;
		foreach($documentData as $nameModule => $data)
		{
			$availabilityProcess = CBPDocument::GetDocumentState($data['DOCUMENT_ID'], $workflowId);
			if(!empty($availabilityProcess))
			{
				if(CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::CreateWorkflow,
					$this->getUser()->getId(),
					$data['DOCUMENT_ID'])
				)
				{
					CBPTrackingService::deleteByWorkflow($workflowId);
					CBPTaskService::deleteByWorkflow($workflowId);
					/** @noinspection PhpDynamicAsStaticMethodCallInspection */
					CBPStateService::deleteWorkflow($workflowId);
				}
			}
		}
		$this->sendJsonSuccessResponse();
	}

	public function processActionStopBizProc($fileId, $idBizProc)
	{
		if (!Loader::includeModule("bizproc"))
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_BIZPROC_LOAD')));
			$this->sendJsonErrorResponse();
		}

		/** @var File $file */
		$file = File::loadById($fileId);
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}

		$documentData = array(
			'DISK' => array(
				'DOCUMENT_ID' => \Thurly\Disk\BizProcDocument::getDocumentComplexId($fileId),
			),
			'WEBDAV' => array(
				'DOCUMENT_ID' => \Thurly\Disk\BizProcDocumentCompatible::getDocumentComplexId($fileId),
			),
		);

		$webdavFileId = $file->getXmlId();
		if(!empty($webdavFileId))
		{
			$documentData['OLD_FILE_COMMON'] = array(
				'DOCUMENT_ID' => array('webdav', 'CIBlockDocumentWebdav', $webdavFileId),
			);
			$documentData['OLD_FILE_GROUP'] = array(
				'DOCUMENT_ID' => array('webdav', 'CIBlockDocumentWebdavSocnet', $webdavFileId),
			);
		}
		$workflowId = $idBizProc;
		$error = array();
		foreach($documentData as $nameModule => $data)
		{
			$availabilityProcess = CBPDocument::GetDocumentState($data['DOCUMENT_ID'], $workflowId);
			if(!empty($availabilityProcess))
			{
				if(CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::StartWorkflow,
					$this->getUser()->getId(),
					$data['DOCUMENT_ID'])
				)
				{
					CBPDocument::TerminateWorkflow(
						$workflowId,
						$data['DOCUMENT_ID'],
						$error
					);
				}
			}
		}
		if($error)
		{
			$this->errorCollection->addOne(new Error(array_shift($error)));
			$this->sendJsonErrorResponse();
		}
		$this->sendJsonSuccessResponse();
	}

	protected function processActionSaveUserField($fileId)
	{
		global $USER_FIELD_MANAGER;

		/** @var File $file */
		$file = File::loadById((int)$fileId);
		if(!$file)
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_FIND_OBJECT));
			$this->sendJsonErrorResponse();
		}
		$securityContext = $file->getStorage()->getCurrentUserSecurityContext();
		if(!$file->canUpdate($securityContext))
		{
			$this->errorCollection->addOne(new Error(Loc::getMessage('DISK_FILE_VIEW_ERROR_COULD_NOT_FIND_OBJECT'), self::ERROR_COULD_NOT_UPDATE_FILE));
			$this->sendJsonErrorResponse();
		}

		$ufFields = array();
		$userFieldManager = Driver::getInstance()->getUserFieldManager();
		$ufEntityName = $userFieldManager->getUfEntityName($file);

		$USER_FIELD_MANAGER->editFormAddFields($ufEntityName, $ufFields);
		foreach ($userFieldManager->getFieldsForObject($file) as $ufName => $ufMetaData)
		{
			if($ufMetaData['USER_TYPE_ID'] !== 'file')
			{
				continue;
			}

			if(isset($ufFields[$ufName]))
			{
				if($ufMetaData['MULTIPLE'] === 'Y')
				{
					foreach($ufFields[$ufName] as $key => $value)
					{
						if(!is_array($value))
						{
							$ufFields[$ufName][$key] = '';
						}
					}
				}
				else
				{
					if(!is_array($ufFields[$ufName]))
					{
						$ufFields[$ufName] = '';
					}
				}
			}
		}
		if(!$USER_FIELD_MANAGER->checkFields($ufEntityName, $file->getId(), $ufFields))
		{
			$e = $this->getApplication()->getException();
			foreach($e->messages as $msg)
			{
				$this->errorCollection->addOne(new \Thurly\Disk\Internals\Error\Error($msg));
			}
			//todo show notice.
			$this->sendJsonErrorResponse();
		}
		$USER_FIELD_MANAGER->update($ufEntityName, $file->getId(), $ufFields, $this->getUser()->getId());

		$this->sendJsonSuccessResponse();
	}
}
$controller = new DiskFileViewAjaxController();
$controller
	->setActionName(\Thurly\Main\Application::getInstance()->getContext()->getRequest()->getQuery('action'))
	->exec()
;