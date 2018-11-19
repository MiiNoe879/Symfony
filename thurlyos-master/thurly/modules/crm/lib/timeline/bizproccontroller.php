<?php
namespace Thurly\Crm\Timeline;

use Thurly\Main;

class BizprocController extends EntityController
{
	//region Singleton
	/** @var $this|null */
	protected static $instance = null;
	/**
	 * @return $this
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	//endregion
	//region BizprocController
	public function onWorkflowStatusChange($workflowId, $status)
	{
		if(!$workflowId)
		{
			throw new Main\ArgumentException('Workflow ID is empty.', 'workflowId');
		}

		if ($status !== \CBPWorkflowStatus::Created
			&& $status !== \CBPWorkflowStatus::Completed
			&& $status !== \CBPWorkflowStatus::Terminated
		)
		{
			return;
		}

		$fields = \CBPStateService::getWorkflowStateInfo($workflowId);

		if(!is_array($fields))
		{
			return;
		}

		list($entityTypeName, $entityId) = explode('_', $fields['DOCUMENT_ID'][2]);
		$entityTypeId = \CCrmOwnerType::ResolveID($entityTypeName);

		$historyEntryID = BizprocEntry::create(
			array(
				'AUTHOR_ID' => self::resolveCreatorID($fields),
				'SETTINGS' => array (
					'WORKFLOW_ID' => $fields['ID'],
					'WORKFLOW_TEMPLATE_ID' => $fields['WORKFLOW_TEMPLATE_ID'],
					'WORKFLOW_TEMPLATE_NAME' => $fields['WORKFLOW_TEMPLATE_NAME'],
					'WORKFLOW_STATUS' => $status,
					'WORKFLOW_STATUS_NAME' => \CBPWorkflowStatus::Out($status),
				),
				'BINDINGS' => array(
					array(
						'ENTITY_TYPE_ID' => $entityTypeId,
						'ENTITY_ID' => $entityId
					)
				)
			)
		);

		$enableHistoryPush = $historyEntryID > 0;
		if($enableHistoryPush && Main\Loader::includeModule('pull'))
		{
			$pushParams = array();
			if($enableHistoryPush)
			{
				$historyFields = TimelineEntry::getByID($historyEntryID);
				if(is_array($historyFields))
				{
					$pushParams['HISTORY_ITEM'] = $this->prepareHistoryDataModel(
						$historyFields,
						array('ENABLE_USER_INFO' => true)
					);
				}
			}

			$tag = TimelineEntry::prepareEntityPushTag($entityTypeId, $entityId);
			\CPullWatch::AddToStack(
				$tag,
				array(
					'module_id' => 'crm',
					'command' => 'timeline_bizproc_status',
					'params' => array_merge($pushParams, array('TAG' => $tag)),
				)
			);
		}
	}
	protected static function resolveCreatorID(array $fields)
	{
		$authorID = 0;
		if(isset($fields['STARTED_BY']))
		{
			$authorID = (int)$fields['STARTED_BY'];
		}

		if($authorID <= 0)
		{
			//Set portal admin as default creator
			$authorID = 1;
		}

		return $authorID;
	}

	public function prepareHistoryDataModel(array $data, array $options = null)
	{
		$settings = isset($data['SETTINGS']) ? $data['SETTINGS'] : array();
		$data['WORKFLOW_TEMPLATE_NAME'] = $settings['WORKFLOW_TEMPLATE_NAME'];
		$data['WORKFLOW_STATUS_NAME'] = $settings['WORKFLOW_STATUS_NAME'];
		unset($data['SETTINGS']);
		return parent::prepareHistoryDataModel($data, $options);
	}
	//endregion
}