<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Util\Replicator\Task;

use Thurly\Main\Localization\Loc;

use Thurly\Tasks\Item;
use Thurly\Tasks\Item\Result;
use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Util\User;
use Thurly\Tasks\Util;
use Thurly\Tasks\UI;
use Thurly\Tasks\Item\Task\Template;
use Thurly\Tasks\Item\SystemLog;

Loc::loadMessages(__FILE__);

final class FromTemplate extends Util\Replicator\Task
{
	private $disabledAC = null;

	protected static function getSourceClass()
	{
		return Template::getClass();
	}

	protected static function getConverterClass()
	{
		return Item\Converter\Task\Template\ToTask::getClass();
	}

	/**
	 * Create sub-tasks for $destination task based on sub-templates of $source
	 *
	 * @param $source
	 * @param $destination
	 * @param array $parameters
	 * @param int $userId
	 * @return Result
	 */
	public function produceSub($source, $destination, array $parameters = array(), $userId = 0)
	{
		$result = new Result();

		Template::enterBatchState();

		$source = $this->getSourceInstance($source, $userId);
		$destination = $this->getDestinationInstance($destination, $userId);

		$created = new Collection();
		$wereErrors = false;

		$destinations = array(
			$destination // root task
		);

		// in case of multitasking create several destinations
		if($this->isMultitaskSource($source, $parameters))
		{
			// create duplicates of $destination for each sub-responsible
			foreach($source['RESPONSIBLES'] as $responsibleId)
			{
				if($responsibleId == $destination['CREATED_BY'])
				{
					continue; // skip creator itself
				}

				$subResult = $this->saveItemFromSource($source, array(
					'PARENT_ID' => $destination->getId(),
					'RESPONSIBLE_ID' => $responsibleId,
				), $userId);

				if($subResult->isSuccess())
				{
					$destinations[] = $subResult->getInstance();
				}
				else
				{
					$wereErrors = true;
				}

				$created->push($subResult);
			}
		}

		// now for each destination create sub-tasks according to the sub-templates, if any
		$data = $this->getSubItemData($source->getId());

		if(!empty($data)) // has sub-templates
		{
			$order = $this->getCreationOrder($data, $source->getId(), $destination->getId());

			if(!$order)
			{
				$result->getErrors()->add('ILLEGAL_STRUCTURE.LOOP', Loc::getMessage('TASKS_REPLICATOR_SUBTREE_LOOP'));
			}
			else
			{
				// disable copying disk files for each sub-task
				// todo: impove this later
				$this->getConverter()->setConfig('UF.FILTER', array('!=USER_TYPE_ID' => 'disk_file'));

				foreach($destinations as $destination)
				{
					//////////////////////////////

					$src2dstId = array($source->getId() => $destination->getId());

					$cTree = $order;

					$walkQueue = array($source->getId());
					while(!empty($walkQueue)) // walk sub-item tree
					{
						$topTemplate = array_shift($walkQueue);

						if(is_array($cTree[$topTemplate]))
						{
							// create all sub template on that tree level
							foreach($cTree[$topTemplate] as $template)
							{
								$dataMixin = array_merge(array(
									'PARENT_ID' => $src2dstId[$topTemplate],
								), $parameters);

								$creationResult = $this->saveItemFromSource($data[$template], $dataMixin, $userId);
								if($creationResult->isSuccess())
								{
									$walkQueue[] = $template; // walk further on that template
									$src2dstId[$template] = $creationResult->getInstance()->getId();
								}
								else
								{
									$wereErrors = true;
								}

								$created->push($creationResult); // add sub-item creation result
							}
						}
						unset($cTree[$topTemplate]);
					}

					//////////////////////////////
				}

				if($wereErrors)
				{
					$result->addError('SUB_ITEMS_CREATION_FAILURE', 'Some of the sub-tasks was not properly created');
				}

				$result->setData($created);
			}
		}

		Template::leaveBatchState();

		return $result;
	}

	/**
	 * @param $id
	 * @param $userId
	 * @return Item
	 */
	protected function makeSourceInstance($id, $userId)
	{
		/** @var Item $itemClass */
		$itemClass = static::getSourceClass();

		/** @var Item $item */
		$item = new $itemClass(intval($id), $userId);

		if($this->getConfig('DISABLE_SOURCE_ACCESS_CONTROLLER'))
		{
			if($this->disabledAC === null)
			{
				$ac = $item->getAccessController()->spawn();
				$ac->disable();

				$this->disabledAC = $ac;
			}

			$item->setAccessController($this->disabledAC);
		}

		return $item;
	}

	/**
	 * Agent handler for repeating tasks.
	 * Create new task based on given template.
	 *
	 * @param integer $templateId - id of task template
	 * @param mixed[] $parameters
	 *
	 * @return string empty string.
	 */
	public static function repeatTask($templateId, array $parameters = array())
	{
		$templateId = (int) $templateId;
		if(!$templateId)
		{
			return ''; // delete agent
		}

		static::liftLogAgent();

		$rsTemplate = \CTaskTemplates::getList(array(),  array('ID' => $templateId), false, false, array('*', 'UF_*')); // todo: replace this with item\orm call
		$arTemplate = $rsTemplate->Fetch();

		if($arTemplate && $arTemplate['REPLICATE'] == 'Y')
		{
			$agentName = str_replace('#ID#', $templateId, $parameters['AGENT_NAME_TEMPLATE']); // todo: when AGENT_NAME_TEMPLATE is not set?

			$result = new \Thurly\Tasks\Util\Replicator\Result();
			if(is_array($parameters['RESULT']))
			{
				$parameters['RESULT']['RESULT'] = $result;
			}

			$createMessage = '';
			$taskId = 0;
			$resumeReplication = true;
			$replicationCancelReason = '';

			// get effective user is (actually, admin)
			$userId = static::getEffectiveUser();
			if(!$userId)
			{
				$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_WAS_NOT_CREATED');
				$result->addError('REPLICATION_FAILED', Loc::getMessage('TASKS_REPLICATOR_CANT_IDENTIFY_USER'));
			}

			// check if CREATOR is alive
			if(!User::isActive($arTemplate['CREATED_BY']))
			{
				$resumeReplication = false; // no need to make another try
				$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_WAS_NOT_CREATED');
				$result->addError('REPLICATION_FAILED', Loc::getMessage('TASKS_REPLICATOR_CREATOR_INACTIVE'));
			}

			// create task if no error occured
			if($result->isSuccess())
			{
				// todo: remove this spike
				$userChanged = false;
				$origUser = null;
				if (intval($arTemplate['CREATED_BY']))
				{
					$userChanged = true;
					$origUser = User::getOccurAsId();
					User::setOccurAsId($arTemplate['CREATED_BY']); // not admin in logs, but template creator
				}

				try
				{
					/** @var \Thurly\Tasks\Util\Replicator\Task $replicator */
					$replicator = new static();
					$replicator->setConfig('DISABLE_SOURCE_ACCESS_CONTROLLER', true); // do not query rights and do not check it
					$produceResult = $replicator->produce($templateId, $userId);

					if($produceResult->isSuccess())
					{
						static::incrementReplicationCount($templateId);

						$task = $produceResult->getInstance();
						$subInstanceResult = $produceResult->getSubInstanceResult();

						$result->setInstance($task);
						if(Collection::isA($subInstanceResult))
						{
							$result->setSubInstanceResult($produceResult->getSubInstanceResult());
						}

						$taskId = $task->getId();

						if($produceResult->getErrors()->isEmpty())
						{
							$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_CREATED');
						}
						else
						{
							$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_CREATED_WITH_ERRORS');
						}

						if($taskId)
						{
							$createMessage .= ' (#'.$taskId.')';
						}
					}
					else
					{
						$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_WAS_NOT_CREATED');
					}

					$result->adoptErrors($produceResult);
				}
				catch(\Exception $e) // catch EACH exception, as we dont want the agent to repeat every 10 minutes in case of smth is wrong
				{
					$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_POSSIBLY_WAS_NOT_CREATED');
					if($taskId)
					{
						$createMessage = Loc::getMessage('TASKS_REPLICATOR_TASK_CREATED_WITH_ERRORS').' (#'.$taskId.')';
					}

					$result->addException($e, Loc::getMessage('TASKS_REPLICATOR_INTERNAL_ERROR'));
				}

				// switch an original hit user back, unless we want some strange things to happen
				if($userChanged)
				{
					User::setOccurAsId($origUser);
				}
			}

			if($createMessage !== '')
			{
				static::sendToSysLog($templateId, intval($taskId), $createMessage, $result->getErrors());
			}

			//////////////
			// calculate next execution time

			$arTemplate['REPLICATE_PARAMS'] = unserialize($arTemplate['REPLICATE_PARAMS']);

			// get agent time
			$agentTime = false;
			$agent = \CAgent::getList(array(), array('NAME' => $agentName))->fetch();
			if($agent)
			{
				$agentTime = $agent['NEXT_EXEC']; // user time
			}

			// get next time task will be created, i.e. next Thursday if today is Thursday (and task marked as repeated) and so on;

			if($resumeReplication)
			{
				$tzCurrent = User::getTimeZoneOffsetCurrentUser();
				$lastTime = $agentTime;
				$iterations = 0;
				do
				{
					$nextResult = static::getNextTime($arTemplate, $lastTime);
					$nextData = $nextResult->getData();
					$nextTime = $nextData['TIME'];

					// next time is legal, but goes before or equals to the current time
					if(($nextTime && MakeTimeStamp($lastTime) >= MakeTimeStamp($nextTime)) || ($iterations > 10000))
					{
						if($iterations > 10000)
						{
							$message = 'insane iteration count reached while calculating next execution time';
						}
						else
						{
							$creator = $arTemplate['CREATED_BY'];
							$tzCreator =  User::getTimeZoneOffset($arTemplate['CREATED_BY']);

							$eDebug = array(
								$creator,
								time(),
								$tzCurrent,
								$tzCreator,
								$arTemplate['REPLICATE_PARAMS']['TIME'],
								$arTemplate['REPLICATE_PARAMS']['TIMEZONE_OFFSET'],
								$iterations
							);
							$message = 'getNextTime() loop detected for replication by template '.$templateId.' ('.$nextTime.' => '.$lastTime.') ('.implode(', ', $eDebug).')';
						}

						Util::log($message); // write to b24 exception log
						static::sendToSysLog($templateId, 0, Loc::getMessage('TASKS_REPLICATOR_PROCESS_ERROR'), null, true);

						$nextTime = false; // possible endless loop, this agent must be stopped
						break;
					}

					// $nextTime in current user`s time (or server time, if no user)

					$lastTime = $nextTime;
					// we can compare one user`s time only with another user`s time, we canna just take time() value
					$cTime = time() + $tzCurrent;

					$iterations++;
				}
				while(($nextResult->isSuccess() && $nextTime) && MakeTimeStamp($nextTime) < $cTime);

				if ($nextTime)
				{
					// we can not use CAgent::Update() here, kz the agent will be updated again just after this function ends ...
					global $pPERIOD;

					$nextTimeFormatted = $nextTime;
					$nextTime = MakeTimeStamp($nextTime);
					// still have $nextTime in current user timezone, we need server time now, so:
					$nextTime -= $tzCurrent;

					// ... but we may set some global var called $pPERIOD
					// "why ' - time()'?" you may ask. see CAgent::ExecuteAgents(), in the last sql we got:
					// NEXT_EXEC=DATE_ADD(".($arAgent["IS_PERIOD"]=="Y"? "NEXT_EXEC" : "now()").", INTERVAL ".$pPERIOD." SECOND),
					$pPERIOD = $nextTime - time();

					static::sendToSysLog($templateId, 0, Loc::getMessage('TASKS_REPLICATOR_NEXT_TIME', array(
						'#TIME#' => $nextTimeFormatted.' ('.UI::formatTimezoneOffsetUTC($tzCurrent).')',
						'#PERIOD#' => $pPERIOD,
						'#SECONDS#' => Loc::getMessage('TASKS_REPLICATOR_SECOND_'.UI::getPluralForm($pPERIOD)),
					)));

					return $agentName; // keep agent working
				}
				else
				{
					$firstError = $nextResult->getErrors()->first();
					if($firstError)
					{
						$replicationCancelReason = $firstError->getMessage();
					}
				}
			}

			static::sendToSysLog(
				$templateId,
				0,
				Loc::getMessage('TASKS_REPLICATOR_PROCESS_STOPPED').($replicationCancelReason != '' ? ': '.$replicationCancelReason : '')
			);
		}

		return ''; // agent will be simply deleted
	}

	/**
	 * Calculates next time agent should be scheduled at
	 *
	 * @param array $templateData
	 * @param bool $agentTime
	 * @param bool $nowTime
	 * @return Util\Result
	 */
	public static function getNextTime(array $templateData, $agentTime = false, $nowTime = false)
	{
		$result = new Util\Result();

		if(!is_array($templateData['REPLICATE_PARAMS']))
		{
			$templateData['REPLICATE_PARAMS'] = array();
		}
		$arParams = \CTaskTemplates::parseReplicationParams($templateData['REPLICATE_PARAMS']); // todo: replace with just $template['REPLICATE_PARAMS']

		// get users and their time zone offsets
		$currentTimeZoneOffset = User::getTimeZoneOffsetCurrentUser(); // set 0 to imitate working on agent
		$creatorTimeZoneOffset = 0;
		if(array_key_exists('TIMEZONE_OFFSET', $arParams))
		{
			$creatorTimeZoneOffset = intval($arParams['TIMEZONE_OFFSET']);
		}
		elseif(intval($templateData['CREATED_BY']))
		{
			$creatorTimeZoneOffset = User::getTimeZoneOffset(intval($templateData['CREATED_BY']));
		}

		// prepare base time
		$baseTime = time(); // server time
		if($nowTime)
		{
			$nowTime = MakeTimeStamp($nowTime); // from string to stamp
			if($nowTime) // time parsed normally
			{
				// $agentTime is in current user`s time, but we want server time here
				$nowTime -= $currentTimeZoneOffset;
				$baseTime = $nowTime;
			}
		}

		if($agentTime) // agent were found and had legal next_time
		{
			$agentTime = MakeTimeStamp($agentTime); // from string to stamp
			if($agentTime) // time parsed normally
			{
				// $agentTime is in current user`s time, but we want server time here
				$agentTime -= $currentTimeZoneOffset;
				$baseTime = $agentTime;
			}
		}

		// prepare time limits
		$startTime = 0;
		if($arParams["START_DATE"])
		{
			$startTime = MakeTimeStamp($arParams["START_DATE"]); // from string to stamp
			$startTime -= $creatorTimeZoneOffset; // to server time (initially in $creatorTimeZoneOffset offset)
		}
		$endTime = PHP_INT_MAX; // never ending
		if($arParams["END_DATE"])
		{
			$endTime = MakeTimeStamp($arParams["END_DATE"]); // from string to stamp
			$endTime -= $creatorTimeZoneOffset; // to server time (initially in $creatorTimeZoneOffset offset)
		}

		// now get max of dates and add time
		$baseTime = max($baseTime, $startTime);

		// prepare time to be forced to
		$creatorPreferredTime = UI::parseTimeAmount($arParams["TIME"], 'HH:MI');

		//////////////////////////////////////////////////////////////
		// now calculate next time based on current $baseTime

		// to format suitable for php date functions
		$startDate = date("Y-m-d H:i:s", $baseTime);

		$arPeriods = array("daily", "weekly", "monthly", "yearly");
		$arOrdinals = array("first", "second", "third", "fourth", "last");
		$arWeekDays = array("mon", "tue", "wed", "thu", "fri", "sat", "sun");
		$type = in_array($arParams["PERIOD"], $arPeriods) ? $arParams["PERIOD"] : "daily";

		$date = 0;
		switch ($type)
		{
			case "daily":
				/**
				 * todo: move this code into a separate function, cover with unit tests, like for getWeeklyDate(),
				 * @see \ReplicatorTests::testWeeklyOffset
				 */
				$num = intval($arParams["EVERY_DAY"]) + intval($arParams["DAILY_MONTH_INTERVAL"])*30;

				$date = strtotime($startDate." +".$num." days");

				if ($arParams["WORKDAY_ONLY"] == "Y")
				{
					// get server datetime as string and create an utc-datetime object with this string, as Calendar works only with utc datetime object
					$dateInst = Util\Type\DateTime::createFromUserTimeGmt(UI::formatDateTime($date));
					$calendar = new Util\Calendar();

					if(!$calendar->isWorkTime($dateInst))
					{
						$cwt = $calendar->getClosestWorkTime($dateInst); // get closest time in UTC
						$cwt = $cwt->convertToLocalTime(); // change timezone to server timezone

						$date = $cwt->getTimestamp(); // set server timestamp
					}
				}

				break;

			case "weekly":
				$date = static::getWeeklyDate($startDate, $arParams);
				break;

			case "monthly":
				/**
				 * todo: move this code into a separate function, cover with unit tests, like for getWeeklyDate(),
				 * @see \ReplicatorTests::testWeeklyOffset
				 */
				$subType = $arParams["MONTHLY_TYPE"] == 2 ? "weekday" : "monthday";
				if ($subType == "weekday")
				{
					$ordinal = array_key_exists($arParams["MONTHLY_WEEK_DAY_NUM"], $arOrdinals) ? $arOrdinals[$arParams["MONTHLY_WEEK_DAY_NUM"]] : $arOrdinals[0];
					$weekDay = array_key_exists($arParams["MONTHLY_WEEK_DAY"], $arWeekDays) ? $arWeekDays[$arParams["MONTHLY_WEEK_DAY"]] : $arWeekDays[0];
					$num = intval($arParams["MONTHLY_MONTH_NUM_2"]) > 0 ? intval($arParams["MONTHLY_MONTH_NUM_2"]) : 1;

					$date = strtotime($ordinal." ".$weekDay." of this month");
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($startDate." +".$num." months");
						$date = strtotime($ordinal." ".$weekDay." of ".date("Y-m-d", $date));
					}
				}
				else
				{
					$day = intval($arParams["MONTHLY_DAY_NUM"]) >= 1 && intval($arParams["MONTHLY_DAY_NUM"]) <= 31 ? intval($arParams["MONTHLY_DAY_NUM"]) : 1;
					$num = intval($arParams["MONTHLY_MONTH_NUM_1"]) > 0 ? intval($arParams["MONTHLY_MONTH_NUM_1"]) : 1;

					$date = strtotime(date("Y-m-".sprintf("%02d", $day), strtotime($startDate)));
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($startDate." +".$num." months");
						$date = strtotime(date("Y-m-".sprintf("%02d", $day), $date));
					}
				}
				break;

			case "yearly":
				/**
				 * todo: move this code into a separate function, cover with unit tests, like for getWeeklyDate(),
				 * @see \ReplicatorTests::testWeeklyOffset
				 */
				$subType = $arParams["YEARLY_TYPE"] == 2 ? "weekday" : "monthday";
				if ($subType == "weekday")
				{
					$ordinal = array_key_exists($arParams["YEARLY_WEEK_DAY_NUM"], $arOrdinals) ? $arOrdinals[$arParams["YEARLY_WEEK_DAY_NUM"]] : $arOrdinals[0];
					$weekDay = array_key_exists($arParams["YEARLY_WEEK_DAY"], $arWeekDays) ? $arWeekDays[$arParams["YEARLY_WEEK_DAY"]] : $arWeekDays[0];
					$month = intval($arParams["YEARLY_MONTH_2"]) >= 0 && intval($arParams["YEARLY_MONTH_2"]) < 12 ? intval($arParams["YEARLY_MONTH_2"]) : 0;
					$month += 1;

					$date = strtotime($ordinal." ".$weekDay." of ".date("Y", strtotime($startDate))."-".sprintf("%02d", $month)."-01");
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($ordinal." ".$weekDay." of ".(date("Y", strtotime($startDate)) + 1)."-".sprintf("%02d", $month)."-01");
					}
				}
				else
				{
					$day = intval($arParams["YEARLY_DAY_NUM"]) >= 1 && intval($arParams["YEARLY_DAY_NUM"]) <= 31 ? intval($arParams["YEARLY_DAY_NUM"]) : 1;
					$month = intval($arParams["YEARLY_MONTH_1"]) >= 0 && intval($arParams["YEARLY_MONTH_1"]) < 12 ? intval($arParams["YEARLY_MONTH_1"]) : 0;
					$month += 1;

					$date = strtotime(date("Y", strtotime($startDate))."-".sprintf("%02d", $month)."-".sprintf("%02d", $day));
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime((date("Y", strtotime($startDate)) + 1)."-".sprintf("%02d", $month)."-".sprintf("%02d", $day));
					}
				}
				break;
		}

		$nextTime = $date; // timestamp

		//////////////////////////////////////////////////////////////

		// now check if we can proceed
		if ($nextTime)
		{
			$proceed = true;

			// about end date
			if(array_key_exists("REPEAT_TILL", $arParams) && $arParams['REPEAT_TILL'] != 'endless')
			{
				if($arParams['REPEAT_TILL'] == 'date')
				{
					$proceed = !($endTime && $nextTime > $endTime);

					if(!$proceed)
					{
						$result->addError('STOP_CONDITION.END_DATE_REACHED', Loc::getMessage('TASKS_REPLICATOR_END_DATE_REACHED'));
					}
				}
				elseif($arParams['REPEAT_TILL'] == 'times' && $templateData)
				{
					$proceed = intval($templateData['TPARAM_REPLICATION_COUNT']) < intval($arParams['TIMES']);

					if(!$proceed)
					{
						$result->addError('STOP_CONDITION.LIMIT_REACHED', Loc::getMessage('TASKS_REPLICATOR_LIMIT_REACHED'));
					}
				}
			}

			if($proceed)
			{
				// here we got $nextTime in server timezone
				// to current user time to save under current user`s time
				$nextTime += $currentTimeZoneOffset;

				// creator will get in result:
				$creatorResultAgentTime = $nextTime + $creatorTimeZoneOffset - $currentTimeZoneOffset;
				$creatorResultAgentDate = static::stripTime($creatorResultAgentTime);

				// now we got to set exact time creator specified
				$creatorResultTime = $creatorResultAgentTime - $creatorResultAgentDate;

				if($creatorResultTime != $creatorPreferredTime)
				{
					$creatorResultAgentTime = $creatorResultAgentDate + $creatorPreferredTime;
					$nextTime = $creatorResultAgentTime - $creatorTimeZoneOffset; // to server time

					// ensure we wont get nextTime in past or equal to the input time
					if($nextTime <= $baseTime)
					{
						$nextTime += 86400; // one day forward
					}

					$nextTime += $currentTimeZoneOffset; // current user time
				}
			}
			else
			{
				$nextTime = 0;
			}
		}
		else // $nextTime was not calculated
		{
			if($result->getErrors()->isEmpty())
			{
				$result->addError('STOP_CONDITION.ILLEGAL_NEXT_TIME', Loc::getMessage('TASKS_REPLICATOR_ILLEGAL_NEXT_TIME'));
			}
		}

		$result->setData(array(
			'TIME' => $nextTime ? UI::formatDateTime($nextTime) : '',
		));

		return $result;
	}

	protected static function getWeeklyDate($startDate, $arParams)
	{
		$weekNumber = intval($arParams["EVERY_WEEK"]);
		$currentDay = date("N", strtotime($startDate)); // day 1 - 7

		$days = is_array($arParams["WEEK_DAYS"]) && sizeof(array_filter($arParams["WEEK_DAYS"])) ? $arParams["WEEK_DAYS"] : array(1); // days 1 - 7

		// check if we have "chosen day" ahead, till the end of the week
		$nextDay = false;
		for($i = $currentDay + 1; $i <= 7; $i++)
		{
			if(in_array($i, $days))
			{
				$nextDay = $i;
				break;
			}
		}

		if($nextDay)
		{
			// next available day found, so just move there
			$str = $startDate." +".($nextDay - $currentDay)." days";
			$date = strtotime($str);
		}
		else
		{
			// we are at the end of the week, and there are no chosen days to pick
			// so we skip $weekNumber weeks and add the first available day
			reset($days);
			$firstDay = current($days);
			$restOfWeek = 7 - $currentDay;

			$str = $startDate.' +'.($weekNumber > 1 ? ($weekNumber - 1) : '0').' weeks '.($restOfWeek + $firstDay)." days";
			$date = strtotime($str);
		}

		return $date;
	}

	public static function reInstallAgent($templateId, array $templateData)
	{
		// todo: get rid of use of CTasks one day...
		$name = 'CTasks::RepeatTaskByTemplateId('.$templateId.');';

		// First, remove all agents for this template
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		self::unInstallAgent($templateId);

		// Set up new agent
		if ($templateData['REPLICATE'] === 'Y')
		{
			$nextTimeResult = static::getNextTime($templateData);
			if ($nextTimeResult->isSuccess())
			{
				$nextTimeData = $nextTimeResult->getData();
				$nextTime = $nextTimeData['TIME'];

				if($nextTime)
				{
					/** @noinspection PhpDynamicAsStaticMethodCallInspection */
					\CAgent::addAgent(
						$name,
						'tasks',
						'N',        // is periodic?
						86400,        // interval
						$nextTime,    // datecheck
						'Y',        // is active?
						$nextTime    // next_exec
					);
				}
				else
				{
					static::sendToSysLog(
						$templateId,
						0,
						Loc::getMessage('TASKS_REPLICATOR_PROCESS_STOPPED'). ' '.Loc::getMessage('TASKS_REPLICATOR_PROCESS_ERROR')
					);
				}
			}
		}
	}

	public static function unInstallAgent($id)
	{
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		\CAgent::removeAgent('CTasks::RepeatTaskByTemplateId('.$id.');', 'tasks');

		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		\CAgent::removeAgent('CTasks::RepeatTaskByTemplateId('.$id.', 0);', 'tasks');

		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		\CAgent::removeAgent('CTasks::RepeatTaskByTemplateId('.$id.', 1);', 'tasks');
	}

	/**
	 * Returns true if $source is a multitask template (template with multiple responsibles)
	 *
	 * @param $source
	 * @param array $parameters
	 * @return bool
	 */
	protected function isMultitaskSource($source, array $parameters = array())
	{
		$enabled = !array_key_exists('MULTITASKING', $parameters) || $parameters['MULTITASKING'] != false;

		return
			Item\Task\Template::isA($source) && // it is a template
			$source['MULTITASK'] == 'Y' && // multitask is on in the template
			count($source['RESPONSIBLES']) && // there are responsibles to produce tasks for
			$enabled; // multitasking was not disabled
	}

	/**
	 * Returns sub-templates data (in array format) for the template with ID == $id
	 *
	 * @param $id
	 * @return array
	 */
	private function getSubItemData($id)
	{
		$result = array();

		$id = intval($id);
		if(!$id)
		{
			return $result;
		}

		// todo: move it to \Thurly\Tasks\Item\Task\Template::find(array('select' => array('*', 'SE_CHECKLIST')))
		// todo: do not forget about access controller
		$res = \CTaskTemplates::getList(array('BASE_TEMPLATE_ID' => 'asc'), array('BASE_TEMPLATE_ID' => $id), false, array('INCLUDE_TEMPLATE_SUBTREE' => true), array('*', 'UF_*', 'BASE_TEMPLATE_ID'));
		while($item = $res->fetch())
		{
			if($item['ID'] == $id)
			{
				continue;
			}

			// unpack values
			$item['RESPONSIBLES'] = unserialize($item['RESPONSIBLES']);
			$item['ACCOMPLICES'] = unserialize($item['ACCOMPLICES']);
			$item['AUDITORS'] = unserialize($item['AUDITORS']);
			$item['TAGS'] = unserialize($item['TAGS']);
			$item['REPLICATE_PARAMS'] = unserialize($item['REPLICATE_PARAMS']);
			$item['DEPENDS_ON'] = unserialize($item['DEPENDS_ON']);

			$result[$item['ID']] = $item;
		}

		// get checklist data
		// todo: convert getListByTemplateDependency() to a runtime mixin for the template entity
		$res = \Thurly\Tasks\Internals\Task\Template\CheckListTable::getListByTemplateDependency($id, array(
			'order' => array('SORT' => 'ASC'),
			'select' => array('ID', 'TEMPLATE_ID', 'CHECKED', 'SORT', 'TITLE')
		));
		while($item = $res->fetch())
		{
			if(isset($result[$item['TEMPLATE_ID']]))
			{
				$result[$item['TEMPLATE_ID']]['SE_CHECKLIST'][$item['ID']] = $item;
			}
		}

		return $result;
	}

	/**
	 * Adds some debug info to the system log for the template with ID == $templateId
	 *
	 * @param $templateId
	 * @param $taskId
	 * @param $message
	 * @param Util\Error\Collection|null $errors
	 * @param bool $forceTypeError
	 */
	private static function sendToSysLog($templateId, $taskId, $message, Util\Error\Collection $errors = null, $forceTypeError = false)
	{
		$record = new SystemLog(array(
			'ENTITY_TYPE' => 1,
			'ENTITY_ID' => $templateId,
			'MESSAGE' => $message,
		));
		if($taskId)
		{
			$record['PARAM_A'] = $taskId;
		}

		if($forceTypeError)
		{
			$record['TYPE'] = SystemLog::TYPE_ERROR;
		}
		elseif($errors instanceof Util\Error\Collection && !$errors->isEmpty())
		{
			$record['TYPE'] = $errors->find(array('TYPE' => Util\Error::TYPE_FATAL))->isEmpty() ? SystemLog::TYPE_WARNING : SystemLog::TYPE_ERROR;
		}

		$record['ERROR'] = $errors;
		$record->save();
	}

	/**
	 * Increments replication counter of the template with ID == $templateId
	 *
	 * @param $templateId
	 */
	private static function incrementReplicationCount($templateId)
	{
		// todo: replace the following with $template->incrementReplicationCount()->save() when ready

		$template = Item\Task\Template::getInstance($templateId, static::getEffectiveUser());
		$templateInst = new \CTaskTemplates();
		$templateInst->update($templateId, array(
			'TPARAM_REPLICATION_COUNT' => intval($template['TPARAM_REPLICATION_COUNT']) + 1
		));
	}

	private static function getEffectiveUser()
	{
		return User::getAdminId();
	}

	private static function stripTime($nextTime)
	{
		$m = (int) date("n", $nextTime);
		$d = (int) date("j", $nextTime);
		$y = (int) date("Y", $nextTime);

		return mktime(0, 0, 0, $m, $d, $y);
	}

	private static function printDebugTime($l, $t)
	{
		Util::printDebug($l.UI::formatDateTime($t));
	}

	/**
	 * Check if template->sub-templates relation tree is correct and return it
	 *
	 * @param array $subEntitiesData
	 * @param $srcId
	 * @return array|bool
	 */
	private function getCreationOrder(array $subEntitiesData, $srcId)
	{
		$walkQueue = array($srcId);
		$treeBundles = array();

		foreach($subEntitiesData as $subTemplate)
		{
			$treeBundles[$subTemplate['BASE_TEMPLATE_ID']][] = $subTemplate['ID'];
		}

		$tree = $treeBundles;
		$met = array();
		while(!empty($walkQueue))
		{
			$topTemplate = array_shift($walkQueue);
			if(isset($met[$topTemplate])) // hey, i`ve met this guy before!
			{
				return false;
			}
			$met[$topTemplate] = true;

			if(is_array($treeBundles[$topTemplate]))
			{
				foreach($treeBundles[$topTemplate] as $template)
				{
					$walkQueue[] = $template;
				}
			}
			unset($treeBundles[$topTemplate]);
		}

		return $tree;
	}

	private static function liftLogAgent()
	{
		\Thurly\Tasks\Util\AgentManager::checkAgentIsAlive('rotateSystemLog', 259200);
	}
}