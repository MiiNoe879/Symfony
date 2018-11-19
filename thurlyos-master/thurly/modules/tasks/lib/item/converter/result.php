<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Item\Converter;

use Thurly\Tasks\Item\Converter;

final class Result extends \Thurly\Tasks\Item\Result
{
	/** @var Converter */
	protected $converter = null;

	public function setConverter($converter)
	{
		$this->converter = $converter;
	}

	public function abortConversion()
	{
		$instance = $this->instance;
		$converter = $this->converter;
		if($instance && $converter)
		{
			return $converter->abortConversion($instance);
		}

		$result = new \Thurly\Tasks\Item\Result();
		$result->addError('NO_CONVERSION', 'No conversion performed before');

		return $result;
	}
}