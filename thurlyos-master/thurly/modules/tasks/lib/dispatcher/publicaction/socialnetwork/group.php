<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 * 
 * @access private
 * 
 * Each method you put here you`ll be able to call as ENTITY_NAME.METHOD_NAME, so be careful.
 */

namespace Thurly\Tasks\Dispatcher\PublicAction\Socialnetwork;

final class Group extends \Thurly\Tasks\Dispatcher\PublicAction
{
	/**
	 * Get a social network group by ID
	 */
	/*
	public function get($id)
	{
		$result = array();

		$id = intval($id);

		if(!$id)
		{
			$this->errors->add('ILLEGAL_GROUP_ID', 'Illegal group id');
		}
		else
		{
			$data = \CSocNetGroup::GetByID($id, $bCheckPermissions = false);
			if(is_array($data) && !empty($data))
			{
				$result = $data;
			}
		}

		return $result;
	}
	*/
}