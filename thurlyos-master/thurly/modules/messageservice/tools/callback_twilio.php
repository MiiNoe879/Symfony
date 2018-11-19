<?
define("NOT_CHECK_PERMISSIONS", true);
define("EXTRANET_NO_REDIRECT", true);
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define('BX_SECURITY_SESSION_READONLY', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if(
	!isset($_POST['SmsSid'])
	|| !isset($_POST['SmsStatus'])
	|| !preg_match('|[A-Z0-9]{34}|i', $_POST['SmsSid'])
	||!CModule::IncludeModule("messageservice")
)
{
	die();
}

$messageId = $_POST['SmsSid'];
$messageStatus = \Thurly\MessageService\Sender\Sms\Twilio::resolveStatus($_POST['SmsStatus']);

if ($messageStatus === null)
	die();

$message = \Thurly\MessageService\Internal\Entity\MessageTable::getList(array(
	'select' => array('ID'),
	'filter' => array(
		'=SENDER_ID' => 'twilio',
		'=EXTERNAL_ID' => $messageId
	)
))->fetch();

if ($message)
{
	\Thurly\MessageService\Internal\Entity\MessageTable::update($message['ID'], array('STATUS_ID' => $messageStatus));
	$message['STATUS_ID'] = $messageStatus;
	\Thurly\MessageService\Integration\Pull::onMessagesUpdate(array($message));
}
CMain::FinalActions();
die();