<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task\Template;

use Thurly\Tasks\Internals\Task\Template\CheckListTable;
use Thurly\Tasks\Item\Task\Template;
use Thurly\Tasks\UI;

/**
 * Class CheckList
 * @package Thurly\Tasks\Item\Task\Template
 *
 * todo: implement here additional fields: IS_COMPLETE and SORT_INDEX, to be able to copy directly task checklist to template checklist
 */

final class CheckList extends \Thurly\Tasks\Item\SubItem
{
	public static function getParentConnectorField()
	{
		return 'TEMPLATE_ID';
	}

	public static function getDataSourceClass()
	{
		return CheckListTable::getClass();
	}

	public static function getCollectionClass()
	{
		return \Thurly\Tasks\Item\Task\Template\Collection\CheckList::getClass();
	}

	protected static function getParentClass()
	{
		return Template::getClass();
	}

	public static function findByParent($parentId, array $parameters = array(), $settings = null)
	{
		if(!array_key_exists('order', $parameters))
		{
			$parameters['order'] = array('SORT' => 'asc');
		}

		return parent::findByParent($parentId, $parameters, $settings);
	}

	public function getFieldTitleHTML()
	{
		return UI::convertBBCodeToHtmlSimple($this['TITLE']);
	}

	public function canToggle()
	{
		return $this->canUpdate();
	}

	public function isCompleted()
	{
		return !!$this['CHECKED'];
	}
}