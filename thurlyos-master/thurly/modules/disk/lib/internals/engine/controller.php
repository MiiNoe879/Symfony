<?php

namespace Thurly\Disk\Internals\Engine;

use \Thurly\Main\Engine;
use Thurly\Main\EventManager;

class Controller extends Engine\Controller
{
	protected function init()
	{
		parent::init();

		Engine\Binder::registerParameterDependsOnName(
			\Thurly\Disk\Internals\Model::className(),
			function($className, $id){
				/** @var \Thurly\Disk\Internals\Model $className */
				return $className::getById($id);
			}
		);
	}

	protected function processAfterAction(Engine\Action $action, $result)
	{
		if ($this->errorCollection->getErrorByCode(Engine\ActionFilter\Csrf::ERROR_INVALID_CSRF))
		{
			return Engine\Response\AjaxJson::createDenied()->setStatus('403 Forbidden');
		}

		return $result;
	}
}