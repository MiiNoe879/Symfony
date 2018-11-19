<?php


namespace Thurly\Main\Engine\ActionFilter;


use Thurly\Main\Engine\Action;
use Thurly\Main\Engine\Controller;
use Thurly\Main\Error;
use Thurly\Main\ErrorCollection;
use Thurly\Main\Errorable;
use Thurly\Main\Event;

abstract class Base implements Errorable
{
	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var Action */
	protected $action;

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	final public static function className()
	{
		return get_called_class();
	}

	/**
	 * Constructor Controller.
	 */
	public function __construct()
	{
		$this->errorCollection = new ErrorCollection;
	}

	final public function bindAction(Action $action)
	{
		$this->action = $action;

		return $this;
	}

	/**
	 * @return Action
	 */
	final public function getAction()
	{
		return $this->action;
	}

	/**
	 * List allowed values of scopes where the filter should work.
	 * @return array
	 */
	public function listAllowedScopes()
	{
		return array(
			Controller::SCOPE_REST,
			Controller::SCOPE_AJAX,
			Controller::SCOPE_CLI,
		);
	}

	public function onBeforeAction(Event $event)
	{
	}

	public function onAfterAction(Event $event)
	{
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}