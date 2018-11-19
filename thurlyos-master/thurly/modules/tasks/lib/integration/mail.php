<?
/**
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration;

use Thurly\Tasks\Integration\Disk;

abstract class Mail extends \Thurly\Tasks\Integration
{
	const MODULE_NAME = 'mail';

	public static function getSubjectPrefix()
	{
		return 'Re: ';
	}

	public static function formatThreadId($tag, $siteId = '')
	{
		$site = \Thurly\Tasks\Util\Site::get($siteId);

		return '<TASKS_'.trim($tag).'@'.$site["SERVER_NAME"].'>';
	}

	public static function getDefaultEmailFrom($siteId = '')
	{
		if(!static::includeModule())
		{
			return '';
		}

		$site = \Thurly\Tasks\Util\Site::get($siteId);

		return \Thurly\Mail\User::getDefaultEmailFrom($site['SERVER_NAME']);
	}

	public static function stopMailEventCompiler()
	{
		if(static::includeModule())
		{
			\Thurly\Main\Mail\EventMessageThemeCompiler::stop();
		}
	}

	protected static function processAttachments($message, array $attachments, $userId)
	{
		// save attachments
		$files = array();
		$relations = array();
		if(is_array($attachments))
		{
			foreach($attachments as $key => $file)
			{
				$uResult = Disk::uploadFile($file, $userId);
				if($uResult->isSuccess())
				{
					$uData = $uResult->getData();

					$files[] = $relations[$key] = $uData['ATTACHMENT_ID'];
				}
			}
		}

		// also, translate possible [DISK FILE] tags in the message, if any
		$message = static::translateRawAttachments($message, $relations);

		return array(
			$message,
			$files
		);
	}

	private static function translateRawAttachments($message, $attachmentRelations)
	{
		if((string) $message == '')
		{
			return $message;
		}

		return preg_replace_callback(
			"/\[ATTACHMENT\s*=\s*([^\]]*)\]/is".BX_UTF_PCRE_MODIFIER,
			function ($matches) use ($attachmentRelations)
			{
				if (isset($attachmentRelations[$matches[1]]))
				{
					return "[DISK FILE ID=".$attachmentRelations[$matches[1]]."]";
				}
			},
			$message
		);
	}
}