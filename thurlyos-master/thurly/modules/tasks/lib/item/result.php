<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Item;

class Result extends \Thurly\Tasks\Util\Result
{
	protected $instance = null;

	/**
	 * @param \Thurly\Tasks\Item $instance
	 */
	public function setInstance($instance)
	{
		if(is_object($instance))
		{
			$this->instance = $instance;
		}
	}

	/**
	 * @return \Thurly\Tasks\Item
	 */
	public function getInstance()
	{
		return $this->instance;
	}
}