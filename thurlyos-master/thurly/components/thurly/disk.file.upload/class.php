<?php
use Thurly\Disk\Internals\DiskComponent;
use Thurly\Main\ArgumentException;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CDiskFileUploadComponent extends DiskComponent
{
	protected $editMode = false;

	protected function prepareParams()
	{
		//fix for compatible
		if(
			empty($this->arParams['STORAGE']) &&
			empty($this->arParams['STORAGE_MODULE_ID']) &&
			!empty($this->arParams['FOLDER'])
		)
		{
			if(!($this->arParams['FOLDER'] instanceof \Thurly\Disk\Folder))
			{
				throw new ArgumentException('FOLDER must be instance of \Thurly\Disk\Folder');
			}
			$this->arParams['STORAGE'] = $this->arParams['FOLDER']->getStorage();
		}

		return parent::prepareParams();
	}

	protected function processActionDefault()
	{
		/**
		 * @var \Thurly\Disk\Folder $folder
		 */
		$folder = $this->arParams['FOLDER'];
		if ($folder && !$folder->canAdd($this->storage->getCurrentUserSecurityContext()))
		{
			return;
		}
		if($this->storage->isEnabledBizProc() && \Thurly\Disk\Integration\BizProcManager::isAvailable())
		{
			$documentData = array(
				'DISK' => \Thurly\Disk\BizProcDocument::generateDocumentComplexType($this->storage->getId()),
				'WEBDAV' => \Thurly\Disk\BizProcDocumentCompatible::generateDocumentComplexType($this->storage->getId()),
			);

			if(!empty($this->arParams['FILE_ID']))
			{
				$autoExecute = CBPDocumentEventType::Edit;
			}
			else
			{
				$autoExecute = CBPDocumentEventType::Create;
			}
			$this->arParams['BIZPROC_PARAMETERS'] = false;
			$this->arParams['BIZPROC_PARAMETERS_REQUIRED'] = array();
			$workflowTemplateId = '';
			foreach($documentData as $nameModule => $data)
			{
				$workflowTemplateObject = CBPWorkflowTemplateLoader::getList(
					array(),
					array("DOCUMENT_TYPE" => $data, "AUTO_EXECUTE" => $autoExecute, "ACTIVE" => "Y"),
					false,
					false,
					array("ID", "PARAMETERS")
				);
				while ($workflowTemplate = $workflowTemplateObject->getNext())
				{
					if(!empty($workflowTemplate['PARAMETERS']))
					{
						foreach($workflowTemplate['PARAMETERS'] as $parametersId => $parameters)
						{
							if($parameters['Required'])
							{
								$this->arParams['BIZPROC_PARAMETERS_REQUIRED'][] = 'bizproc'.$workflowTemplate['ID'].'_'.$parametersId;
							}
						}
						$this->arParams['BIZPROC_PARAMETERS'] = true;
					}
					$workflowTemplateId = $workflowTemplate['ID'];
				}
			}

			$this->arParams['STATUS_START_BIZPROC'] = !empty($workflowTemplateId);
		}

		$this->arParams['STORAGE_ID'] = $this->storage->getId();

		$this->includeComponentTemplate();
	}
}