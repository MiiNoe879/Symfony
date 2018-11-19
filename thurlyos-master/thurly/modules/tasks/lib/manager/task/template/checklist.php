<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 * 
 * @access private
 * 
 * This class should be used in components, inside agent functions, in rest, ajax and more, bringing unification to all places and processes
 */

namespace Thurly\Tasks\Manager\Task\Template;

use \Thurly\Main\Loader;
use \Thurly\Main\Localization\Loc;

use \Thurly\Tasks\Util\Error\Collection;
use \Thurly\Tasks\Template\CheckListTable;
use \Thurly\Tasks\Util\Assert;

Loc::loadMessages(__FILE__);

final class CheckList extends \Thurly\Tasks\Manager\Task\CheckList
{
	public static function getListByParentEntity($userId, $templateId, array $parameters = array())
	{
		$userId = 		Assert::expectIntegerPositive($userId, '$userId');
		$templateId = 	Assert::expectIntegerPositive($templateId, '$templateId');

		$data = array();
		$can = array();

		// todo: implement this:
		/*
		$template = static::getTemplate($userId, $templateId);
		if(empty($template))
		{
			throw new \Thurly\Tasks\ActionNotAllowedException();
		}
		*/

		$res = CheckListTable::getList(array(
			'filter' => array('=TEMPLATE_ID' => $templateId),
			'order' => array('SORT' => 'asc'),
			'select' => array('ID', 'TITLE', 'SORT_INDEX', 'IS_COMPLETE')
		));

		$i = 0;
		while($itemData = $res->fetch())
		{
			if($parameters['DROP_PRIMARY'])
			{
				$itemId = 'n'.$i;
				unset($itemData['ID']);
				$itemCan = static::getFullRights();
			}
			else
			{
				$itemId = $itemData['ID'];
				$itemCan = array(); // no access system for template checklist item currently
			}

			$data[$itemId] = $itemData;
			$data[$itemId][static::ACT_KEY] = $can[$itemId]['ACTION'] = $itemCan;

			$i++;
		}

		return array('DATA' => $data, 'CAN' => $can);
	}

	public static function add($userId, array $data, array $parameters = array('PUBLIC_MODE' => false))
	{
		throw new \Thurly\Main\NotImplementedException();
	}

	public static function update($userId, $itemId, array $data, array $parameters = array('PUBLIC_MODE' => false))
	{
		throw new \Thurly\Main\NotImplementedException();
	}

	// todo: care about PUBLIC_MODE!
	public static function manageSet($userId, $templateId, array $items = array(), array $parameters = array('PUBLIC_MODE' => false, 'MODE' => self::MODE_ADD))
	{
		$errors = static::ensureHaveErrorCollection($parameters);
		$result = array(
			'DATA' => array(),
			'CAN' => array(),
			'ERRORS' => $errors
		);

		if(!static::checkSetPassed($items, $parameters['MODE']))
		{
			return $result;
		}

		// todo: implement this:
		/*
		$template = static::getTemplate($userId, $templateId);
		if(empty($template))
		{
			throw new \Thurly\Tasks\ActionNotAllowedException();
		}
		*/

		$data = array();

		// todo: move \Thurly\Tasks\Template\CheckListItemTable::updateForTemplate() here, to this class, leave proxy method there

		$sort = 0;
		$itemsToUpdate = array();
		foreach($items as $item)
		{
			$id = intval($item['ID']) ? intval($item['ID']) : false;
			$itemData = array(
				'TITLE' => 		$item['TITLE'],
				'CHECKED' => 	$item['IS_COMPLETE'] == 'Y',
				'SORT' => 		$sort++
			);

			if(intval($id) && $parameters['MODE'] != self::MODE_ADD)
			{
				$itemData['ID'] = $id;
				$itemsToUpdate[$id] = $itemData;
			}
			else
			{
				$itemsToUpdate[] = $itemData;
			}
		}

		if(!empty($itemsToUpdate))
		{
			// todo: pass errors here
			$uResult = \Thurly\Tasks\Template\CheckListTable::updateForTemplate($templateId, $itemsToUpdate);
		}

		//$result['DATA'] = $data;

		return $result;
	}
}

