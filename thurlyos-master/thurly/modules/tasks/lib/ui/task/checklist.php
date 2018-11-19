<?php
/**
 * This class contains ui helper for task/checklist entity
 *
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Tasks\UI\Task;

final class CheckList
{
	public static function checkIsSeparatorValue($value)
	{
		$value = trim((string) $value);

		return preg_match('#^(-|=|_|\*|\+){3,}$#', $value);
	}
}