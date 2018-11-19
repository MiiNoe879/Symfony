<?php


namespace Thurly\Disk\Uf;


use Thurly\Disk\AttachedObject;

interface IWorkWithAttachedObject
{
	/**
	 * @param AttachedObject $attachedObject
	 * @return $this
	 */
	public function setAttachedObject(AttachedObject $attachedObject);

	/**
	 * @return AttachedObject
	 */
	public function getAttachedObject();

	/**
	 * @return bool
	 */
	public function isSetAttachedObject();
}