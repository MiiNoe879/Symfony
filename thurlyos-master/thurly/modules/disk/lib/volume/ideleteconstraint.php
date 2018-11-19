<?php

namespace Thurly\Disk\Volume;

interface IDeleteConstraint
{
	/**
	 * Check ability to drop folder.
	 * @param \Thurly\Disk\Folder $folder Folder to drop.
	 * @return boolean
	 */
	public function isAllowDeleteFolder(\Thurly\Disk\Folder $folder);
}
