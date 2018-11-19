<?php
namespace Thurly\Crm\Automation;

use Thurly\Crm\Automation\Engine\DelayInterval;
use Thurly\Disk;
use Thurly\Main\Loader;

class Helper
{
	const CURRENT_DATE_BASIS = '{=System:Date}';
	const CURRENT_DATETIME_BASIS = '{=System:Now}';

	protected static $isBizprocEnabled;
	protected static $maps;

	public static function prepareUserSelectorEntities(array $documentType, $users)
	{
		$result = array();
		if (!static::isBizprocEnabled())
			return $result;

		$users = (array)$users;
		$documentUserFields = static::getDocumentFields($documentType, 'user');

		foreach ($users as $user)
		{
			if (!is_scalar($user))
				continue;

			if (substr($user, 0, 5) === "user_")
			{
				$user = intval(substr($user, 5));
				if (($user > 0) && !in_array($user, $result))
				{
					$result[] = array(
						'id'         => 'U'.$user,
						'entityId'   => $user,
						'name'       => htmlspecialcharsBx(\CCrmViewHelper::GetFormattedUserName($user))
					);
				}
			}
			elseif ($user === 'author' && isset($documentUserFields['ASSIGNED_BY_ID']))
			{
				$result[] = array(
					'id'         => 'BPR_'.$documentUserFields['ASSIGNED_BY_ID']['Id'],
					'entityId'   => $documentUserFields['ASSIGNED_BY_ID']['Expression'],
					'name'       => htmlspecialcharsBx($documentUserFields['ASSIGNED_BY_ID']['Name']),
					'avatar' => '',
					'desc' => '&nbsp;'
				);
			}
			else
			{
				foreach ($documentUserFields as $field)
				{
					if ($user !== $field['Expression'])
						continue;

					$result[] = array(
						'id'         => 'BPR_'.$field['Id'],
						'entityId'   => $field['Expression'],
						'name'       => htmlspecialcharsBx($field['Name']),
						'avatar' => '',
						'desc' => '&nbsp;'
					);
				}
			}
		}
		return $result;
	}

	/**
	 * Get disk files information by file ids.
	 * @param int|array $attachments
	 * @return array
	 */
	public static function prepareDiskAttachments($attachments)
	{
		$result = array();

		if (!Loader::includeModule('disk'))
			return $result;

		foreach ((array)$attachments as $attachmentId)
		{
			$attachmentId = (int)$attachmentId;
			if ($attachmentId <= 0)
			{
				continue;
			}

			$file = Disk\File::loadById($attachmentId);
			if ($file)
			{
				$result[] = array(
					'id' => $file->getId(),
					'name' => $file->getName(),
					'size' => \CFile::FormatSize($file->getSize()),
					'type' => 'disk'
				);
			}
		}

		return $result;
	}

	/**
	 * Get files information from document fields.
	 * @param array $documentType
	 * @param $files
	 * @return array
	 */
	public static function prepareFileAttachments(array $documentType, $files)
	{
		$result = array();
		if (!static::isBizprocEnabled())
			return $result;

		$files = (array)$files;
		$documentUserFields = static::getDocumentFields($documentType, 'file');

		foreach ($files as $file)
		{
			if (!is_scalar($file))
				continue;

			foreach ($documentUserFields as $field)
			{
				if ($file !== $field['Expression'])
					continue;

				$result[] = array(
					'id' => $field['Expression'],
					'name' => $field['Name'],
					'type' => 'file'
				);
			}
		}
		return $result;
	}

	public static function convertExpressions($source, array $documentType)
	{
		$source = (string)$source;
		list($simple, $original) = static::getExpressionsMap($documentType);
		$source = str_replace($original, $simple, $source);
		return $source;
	}

	public static function unConvertExpressions($source, array $documentType)
	{
		$source = (string)$source;
		list($simple, $original) = static::getExpressionsMap($documentType);
		$source = str_replace($simple, $original, $source);
		return $source;
	}

	/**
	 * Get document fields for usage in robots designer.
	 * @param array $documentType Bizproc document type.
	 * @param null|string $typeFilter
	 * @return array
	 */
	public static function getDocumentFields(array $documentType, $typeFilter = null)
	{
		$resultFields = array();
		if (!static::isBizprocEnabled())
			return $resultFields;

		//TODO: static cache

		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		/** @var \CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');
		$documentFields = $documentService->GetDocumentFields($documentType);

		if (is_array($documentFields))
		{
			foreach ($documentFields as $id => $field)
			{
				if ($field['Type'] === 'UF:boolean')
				{
					//Mark as bizproc boolean type
					$field['Type'] = $field['BaseType'] = 'bool';
				}

				if ($field['BaseType'] === 'string' && $field['Type'] !== 'string')
					continue; // Skip none-printable user fields

				if ($typeFilter !== null && $field['Type'] !== $typeFilter)
					continue;

				$resultFields[$id] = array(
					'Id' => $id,
					'Name' => $field['Name'],
					'Type' => $field['Type'],
					'Expression' => '{{'.$field['Name'].'}}',
					'SystemExpression' => '{=Document:'.$id.'}',
					'Options' => $field['Options']
				);
			}
		}
		return $resultFields;
	}

	protected static function getExpressionsMap(array $documentType)
	{
		$key = implode('@', $documentType);
		if (!isset(static::$maps[$key]))
		{
			$simple = array();
			$original = array();

			$fields = static::getDocumentFields($documentType);
			foreach ($fields as $field)
			{
				$simple[] = $field['Expression'];
				$original[] = $field['SystemExpression'];
			}

			static::$maps[$key] = array($simple, $original);
		}
		return static::$maps[$key];
	}

	public static function parseDateTimeInterval($interval)
	{
		$interval = (string)$interval;
		$result = array(
			'basis' => null,
			'i' => 0,
			'h' => 0,
			'd' => 0,
			'workTime' => false
		);

		if (strpos($interval, '=dateadd(') === 0 || strpos($interval, '=workdateadd(') === 0)
		{
			if (strpos($interval, '=workdateadd(') === 0)
			{
				$interval = substr($interval, 13, -1); // cut =workdateadd(...)
				$result['workTime'] = true;
			}
			else
			{
				$interval = substr($interval, 9, -1); // cut =dateadd(...)
			}

			$arguments = explode(',', $interval);
			$result['basis'] = trim($arguments[0]);

			$arguments[1] = trim($arguments[1], '"\'');
			$result['type'] = strpos($arguments[1], '-') === 0 ? DelayInterval::TYPE_BEFORE : DelayInterval::TYPE_AFTER;

			preg_match_all('/\s*([\d]+)\s*(i|h|d)\s*/i', $arguments[1], $matches);
			foreach ($matches[0] as $i => $match)
			{
				$result[$matches[2][$i]] = (int)$matches[1][$i];
			}
		}
		elseif (\CBPDocument::IsExpression($interval))
			$result['basis'] = $interval;

		$minutes = $result['i'] + $result['h'] * 60 + $result['d'] * 60 * 24;

		if ($minutes % 1440 === 0)
		{
			$result['value'] = $minutes / 1440;
			$result['valueType'] = 'd';
		}
		elseif ($minutes % 60 === 0)
		{
			$result['value'] = $minutes / 60;
			$result['valueType'] = 'h';
		}
		else
		{
			$result['value'] = $minutes;
			$result['valueType'] = 'i';
		}

		if (
			!$result['value']
			&& $result['basis'] !== static::CURRENT_DATETIME_BASIS
			&& \CBPDocument::IsExpression($result['basis'])
		)
		{
			$result['type'] =  DelayInterval::TYPE_IN;
		}

		return $result;
	}

	public static function getDateTimeIntervalString($interval)
	{
		if (!$interval['basis'] || !\CBPDocument::IsExpression($interval['basis']))
			$interval['basis'] = static::CURRENT_DATE_BASIS;

		$days = isset($interval['d']) ? (int)$interval['d'] : 0;
		$hours = isset($interval['h']) ? (int)$interval['h'] : 0;
		$minutes = isset($interval['i']) ? (int)$interval['i'] : 0;

		if (isset($interval['value']) && isset($interval['valueType']))
		{
			switch ($interval['valueType'])
			{
				case 'i':
					$minutes = (int)$interval['value'];
					break;
				case 'h':
					$hours = (int)$interval['value'];
					break;
				case 'd':
					$days = (int)$interval['value'];
					break;
			}
		}

		$add = '';
		if (isset($interval['type']) && $interval['type'] == DelayInterval::TYPE_BEFORE)
			$add = '-';

		if ($days > 0)
			$add .= $days.'d';
		if ($hours > 0)
			$add .= $hours.'h';
		if ($minutes > 0)
			$add .= $minutes.'i';

		$fn = !empty($interval['workTime']) ? 'workdateadd' : 'dateadd';

		if ($fn === 'workdateadd' && $add === '')
		{
			$add = '0d';
		}

		return '='.$fn.'('.$interval['basis'].',"'.$add.'")';
	}

	public static function parseTimeString($time)
	{
		$pairs = preg_split('#[\s:]+#', $time);
		$pairs[0] = (int)$pairs[0];
		$pairs[1] = (int)$pairs[1];

		if (count($pairs) === 3)
		{
			if ($pairs[2] == 'pm' && $pairs[0] < 12)
				$pairs[0] += 12;
			if ($pairs[2] == 'am' && $pairs[0] == 12)
				$pairs[0] = 0;
		}

		return array('h' => $pairs[0], 'i' => $pairs[1]);
	}

	public static function isBizprocEnabled()
	{
		if (static::$isBizprocEnabled === null)
		{
			static::$isBizprocEnabled = Loader::includeModule('bizproc');
		}

		return static::$isBizprocEnabled;
	}
}