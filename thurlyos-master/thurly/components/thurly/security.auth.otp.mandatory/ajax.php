<?php
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

use Thurly\Main\Web\Json;
use Thurly\Security\Mfa\Otp;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

header('Content-Type: application/json', true);

$request = Thurly\Main\Context::getCurrent()->getRequest();

if (!CModule::includeModule('security'))
{
	response(array(
		'status' => 'error',
		'error' => Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_MODULE_ERROR')
	));
}

if (!Otp::isOtpRequiredByMandatory())
{
	response(array(
		'status' => 'error',
		'error' =>  Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_NOT_REQUIRED')
	));
}

if ($USER->IsAuthorized())
{
	response(array(
		'status' => 'error',
		'error' => Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_AUTH_ERROR')
	));
}

if (!check_thurly_sessid())
{
	response(array(
		'status' => 'error',
		'error' => 'INVALID_SESSID'
	));
}


switch($request->getPost('action'))
{
	case 'check_activate':
		if (
			$request->getPost('secret') === null
			|| $request->getPost('sync1') === null
		)
		{
			$result = array(
				'status' => 'error',
				'error' => 'NOT_ENOUGH_PARAMS'
			);
		}
		else
		{
			$fields = array(
				'ACTIVE' => 'Y',
				'SECRET' => $_POST['secret'],
				'SYNC1' => $_POST['sync1'],
				'SYNC2' => $_POST['sync2'],
			);

			$result = checkAndActivate($fields);
		}

		response($result);
		break;

	default:
		response(array(
			'status' => 'error',
			'error' => 'ACTION_NOT_FOUND'
		));
}

function response($result)
{
	echo Json::encode($result);
	die;
}

function checkAndActivate($fields)
{
	try
	{
		$deferredParams = Otp::getDeferredParams();
		if (!$deferredParams['USER_ID'])
		{
			throw new \Thurly\Security\Mfa\OtpException(Loc::getMessage('SECURITY_AUTH_OTP_MANDATORY_UNKNOWN_ERROR'));
		}

		$otp = Otp::getByUser($deferredParams['USER_ID']);
		$binarySecret = pack('H*', $fields['SECRET']);
		$otp
			->regenerate($binarySecret)
			->syncParameters($fields['SYNC1'], $fields['SYNC2'])
			->save()
		;

		$deferredParams[Otp::REJECTED_KEY] = OTP::REJECT_BY_CODE;
		Otp::setDeferredParams($deferredParams);

		$result = array(
			'status' => 'ok'
		);
	}
	catch (\Thurly\Security\Mfa\OtpException $e)
	{
		$result = array(
			'status' => 'error',
			'error' => $e->getMessage()
		);
	}

	return $result;
}