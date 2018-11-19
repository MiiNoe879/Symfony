<?php

namespace Thurly\Main\Engine\Component;


use Thurly\Main\Engine\Action;
use Thurly\Main\Engine\Binder;
use Thurly\Main\Engine\Contract\Controllerable;
use Thurly\Main\Engine\Controller;
use Thurly\Main\Errorable;

final class InlineAction extends Action
{
	/**
	 * @var string
	 */
	protected $methodName;
	/**
	 * @var Controllerable
	 */
	private $controllerable;

	/**
	 * InlineAction constructor.
	 *
	 * @param string $name
	 * @param Controllerable $controllerable
	 * @param Controller $controller
	 * @param array $config
	 */
	public function __construct($name, Controllerable $controllerable, Controller $controller, $config = array())
	{
		$this->methodName = $controller->generateActionMethodName($name);
		$this->controllerable = $controllerable;
		parent::__construct($name, $controller, $config);
	}

	protected function buildBinder()
	{
		if ($this->binder === null)
		{
			$this->binder = new Binder(
				$this->controllerable,
				$this->methodName,
				$this->getController()->getSourceParametersList()
			);
		}

		return $this;
	}

	public function runWithSourceParametersList()
	{
		$result = parent::runWithSourceParametersList();

		if ($this->controllerable instanceof Errorable)
		{
			$this->errorCollection->add(
				$this->controllerable->getErrors()
			);
		}

		return $result;
	}
}