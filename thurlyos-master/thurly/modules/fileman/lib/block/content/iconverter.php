<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sender
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Fileman\Block\Content;

use Thurly\Main\Application;
use Thurly\Main\Web\DOM\Document;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Web\DOM\CssParser;
use Thurly\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);

interface IConverter
{
	/**
	 * Check string.
	 *
	 * @param string $string String.
	 * @return bool
	 */
	public static function isValid($string);

	/**
	 * Parse string to an array of content blocks
	 *
	 * @param string $string String.
	 * @return BlockContent
	 */
	public static function toArray($string);
}