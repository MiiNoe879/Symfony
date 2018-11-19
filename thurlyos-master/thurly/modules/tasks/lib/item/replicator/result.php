<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Item\Replicator;

use Thurly\Tasks\Util\Collection;

final class Result extends \Thurly\Tasks\Item\Result
{
	protected $sIResult = null;

	public function setSubInstanceResult(Collection $subInstanceResults)
	{
		$this->sIResult = $subInstanceResults;
	}

	public function getSubInstanceResults()
	{
		return $this->sIResult;
	}
}