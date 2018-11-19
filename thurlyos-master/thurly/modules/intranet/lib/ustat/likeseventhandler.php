<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage intranet
 * @copyright 2001-2013 Thurly
 */

namespace Thurly\Intranet\UStat;

class LikesEventHandler
{
	const SECTION = 'LIKES';

	const TITLE = 'INTRANET_USTAT_SECTION_LIKES_NAME';

	public static function getTitle()
	{
		IncludeModuleLangFile(__FILE__);

		return GetMessage(static::TITLE);
	}

	public static function registerListeners()
	{
		RegisterModuleDependences("main", "OnAddRatingVote", "intranet", "\\".__CLASS__, "onAddRatingVoteEvent");
	}

	public static function unregisterListeners()
	{
		UnRegisterModuleDependences("main", "OnAddRatingVote", "intranet", "\\".__CLASS__, "onAddRatingVoteEvent");
	}

	public static function onAddRatingVoteEvent($ID, $arParams)
	{
		UStat::incrementCounter(static::SECTION);
	}
}