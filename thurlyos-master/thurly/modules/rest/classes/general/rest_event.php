<?
use Thurly\Rest\Event\Sender;

/** @deprecated
 * use \Thurly\Rest\Event\Callback
 */
class CRestEventCallback extends \Thurly\Rest\Event\Callback
{
	public static function __callStatic($name, $arguments)
	{
		$event = Sender::parseEventName($name);

		Sender::unbind($event['MODULE_ID'], $event['EVENT']);
		Sender::bind($event['MODULE_ID'], $event['EVENT']);

		parent::__callStatic($name, $arguments);
	}
}

/** @deprecated
 * use \Thurly\Rest\Event\Session
 */
class CRestEventSession
{
	public static function Get()
	{
		return \Thurly\Rest\Event\Session::get();
	}

	public static function Set($session)
	{
		\Thurly\Rest\Event\Session::set($session);
	}
}

?>