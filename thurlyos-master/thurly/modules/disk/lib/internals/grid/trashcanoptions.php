<?php

namespace Thurly\Disk\Internals\Grid;

use Thurly\Main\Localization\Loc;

/**
 * Class TrashCanOptions
 * @package Thurly\Disk\Internals\Grid
 * @internal
 */
final class TrashCanOptions extends FolderListOptions
{
	/**
	 * Returns grid id.
	 * @return string
	 */
	public function getGridId()
	{
		return 'trashcan_' . $this->storage->getId();
	}

	/**
	 * Returns default sorting.
	 *
	 * @return array
	 */
	public function getDefaultSorting()
	{
		return array('DELETE_TIME' => 'DESC');
	}

	public function getPossibleColumnForSorting()
	{
		return array_merge(
			array(
				'DELETE_TIME' => array(
					'ALIAS' => 'DELETE_TIME',
					'LABEL' => Loc::getMessage('DISK_TRASHCAN_COLUMN_DELETE_TIME')
				),
			),
			parent::getPossibleColumnForSorting()
		);
	}

	/**
	 * Tells if sort mode is mix.
	 * @return bool
	 */
	protected function isMixSortMode()
	{
		return true;
	}
}