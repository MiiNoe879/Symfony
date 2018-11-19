<?

namespace Thurly\Main\Grid;


/**
 * Class Context.
 * @package Thurly\Main\Grid
 */
class Context
{

	/**
	 * @return \Thurly\Main\HttpRequest
	 */
	protected static function getRequest()
	{
		return \Thurly\Main\Context::getCurrent()->getRequest();
	}


	/**
	 * Checks whether the request from grid
	 * @return bool
	 */
	public static function isInternalRequest()
	{
		$request = self::getRequest();
		return (
			($request->get("internal") == true && $request->get("grid_id")) ||
			($request->getPost("internal") == true && $request->getPost("grid_id"))
		);
	}
}