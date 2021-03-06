<?php


namespace Thurly\Disk\Document\CloudImport;


use Thurly\Disk\ThurlyOSDisk;
use Thurly\Disk\Internals\Error\Error;
use Thurly\Disk\Internals\Error\ErrorCollection;
use Thurly\Main\IO;

final class TmpFile extends ThurlyOSDisk\TmpFile
{
	protected static function prepareDataToInsertFromFileArray(array $fileData, array $data, ErrorCollection $errorCollection)
	{
		list($relativePath, $absolutePath) = self::generatePath();

		$file = new IO\File($fileData['tmp_name']);
		if(!$file->isExists())
		{
			$errorCollection->addOne(new Error('Could not find file', self::ERROR_EXISTS_FILE));
			return null;
		}
		if(!$file->rename($absolutePath))
		{
			$errorCollection->addOne(new Error('Could not move file', self::ERROR_MOVE_FILE));
			return null;
		}

		//now you can set CREATED_BY
		$data = array_intersect_key($data, array('CREATED_BY' => true));

		return array_merge(array(
			'TOKEN' => static::generateTokenByPath($relativePath),
			'FILENAME' => $fileData['name'],
			'PATH' => $relativePath,
			'BUCKET_ID' => '',
			'SIZE' => '',
			'IS_CLOUD' => '',
			'WIDTH' => empty($fileData['width'])? '' : $fileData['width'],
			'HEIGHT' => empty($fileData['height'])? '' : $fileData['height'],
		), $data);
	}
}