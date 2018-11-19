<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Util\Replicator;

use Thurly\Tasks\Util\Collection;

final class Result extends \Thurly\Tasks\Item\Result
{
	protected $sIResult = null;

	public function setSubInstanceResult($subInstanceResults)
	{
		$this->sIResult = $subInstanceResults;
	}

	public function getSubInstanceResult()
	{
		return $this->sIResult;
	}
}