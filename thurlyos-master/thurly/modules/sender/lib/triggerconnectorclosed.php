<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sender
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sender;


abstract class TriggerConnectorClosed extends TriggerConnector
{
	/** @return bool */
	final public static function isClosed()
	{
		return true;
	}

	/**
	 * @return string
	 */
	final public function getEventModuleId()
	{
		return 'sender';
	}

	/**
	 * @return string
	 */
	final public function getEventType()
	{
		return $this->getModuleId().'_'.$this->getCode();
	}

	/** @return array */
	final public function getProxyFieldsFromEventToConnector()
	{
		return array();
	}
}