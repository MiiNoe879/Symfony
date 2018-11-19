<?
namespace Thurly\Tasks\Dispatcher;

class Exception	extends \Thurly\Tasks\Exception
{
	public function getDefaultMessage()
	{
		return 'Dispatcher failure';
	}
};

class BadQueryException extends Exception
{
	public function getDefaultMessage()
	{
		return 'Dispatcher failure: bad query';
	}
};