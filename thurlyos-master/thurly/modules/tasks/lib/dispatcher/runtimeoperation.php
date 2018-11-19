<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 *
 * @access private
 *
 * This class DOES NOT check any CSRF tokens and even for current user`s authorization, so BE CAREFUL using it.
 */

namespace Thurly\Tasks\Dispatcher;

use Thurly\Tasks\Util\Result;
use Thurly\Tasks\Util\Error\Collection;

class RunTimeOperation
{
	private $callable = null;
	protected $errors = null;

	public function __construct($operation, array $parameters = array())
	{
		$this->operation = $operation;
		$this->errors = new Collection();

		$this->callable = $parameters['CALLABLE'];
	}

	public function call()
	{
		$opResult = call_user_func_array($this->callable, $this->operation['ARGUMENTS']);

		if($opResult instanceof Result)
		{
			// also get errors from result, in case of object
			$this->errors->load($opResult->getErrors());
			return $opResult->getData();
		}
		else
		{
			return $opResult;
		}
	}

	public function getOperation()
	{
		return $this->operation;
	}

	public function getErrors()
	{
		return $this->errors;
	}

	public function parse()
	{
		if(is_callable($this->callable))
		{
			$reflection = new \ReflectionFunction($this->callable);

			if($reflection->isClosure())
			{
				// todo: check arguments
			}
			else
			{
				// raise an error
				$this->addParseError('Runtime action is not a closure');
			}
		}
		else
		{
			// raise an error
			$this->addParseError('Runtime action is not callable');
		}
	}

	protected function addParseError($message)
	{
		$this->errors->add('PARSE_ERROR', $message, \Thurly\Tasks\Dispatcher::ERROR_TYPE_PARSE);
	}
}