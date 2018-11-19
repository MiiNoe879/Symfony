<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Main\Text;

class HtmlFilter
{
	public static function encode($string, $flags = ENT_COMPAT)
	{
		return htmlspecialchars($string, $flags, (defined("BX_UTF") ? "UTF-8" : "ISO-8859-1"));
	}
}