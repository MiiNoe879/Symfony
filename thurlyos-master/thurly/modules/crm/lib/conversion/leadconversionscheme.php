<?php
namespace Thurly\Crm\Conversion;
use Thurly\Main;
class LeadConversionScheme
{
	const UNDEFINED = 0;
	const DEAL_CONTACT_COMPANY = 1;
	const DEAL_CONTACT = 2;
	const DEAL_COMPANY = 3;
	const DEAL = 4;
	const CONTACT_COMPANY = 5;
	const CONTACT = 6;
	const COMPANY = 7;

	const DEAL_CONTACT_COMPANY_NAME = 'DEAL_CONTACT_COMPANY';
	const DEAL_CONTACT_NAME = 'DEAL_CONTACT';
	const DEAL_COMPANY_NAME = 'DEAL_COMPANY';
	const DEAL_NAME = 'DEAL';
	const CONTACT_COMPANY_NAME = 'CONTACT_COMPANY';
	const CONTACT_NAME = 'CONTACT';
	const COMPANY_NAME = 'COMPANY';

	private static $allDescriptions = array();

	public static function isDefined($schemeID)
	{
		if(!is_numeric($schemeID))
		{
			return false;
		}

		$schemeID = (int)$schemeID;
		return $schemeID >= self::DEAL_CONTACT_COMPANY && $schemeID <= self::COMPANY;
	}
	public static function getDefault()
	{
		return self::DEAL_CONTACT_COMPANY;
	}
	public static function resolveName($schemeID)
	{
		if(!is_numeric($schemeID))
		{
			return '';
		}

		$schemeID = (int)$schemeID;
		if($schemeID <= 0)
		{
			return '';
		}

		switch($schemeID)
		{
			case self::DEAL_CONTACT_COMPANY:
				return self::DEAL_CONTACT_COMPANY_NAME;
			case self::DEAL_CONTACT:
				return self::DEAL_CONTACT_NAME;
			case self::DEAL_COMPANY:
				return self::DEAL_COMPANY_NAME;
			case self::DEAL:
				return self::DEAL_NAME;
			case self::CONTACT_COMPANY:
				return self::CONTACT_COMPANY_NAME;
			case self::CONTACT:
				return self::CONTACT_NAME;
			case self::COMPANY:
				return self::COMPANY_NAME;
			case self::UNDEFINED:
			default:
				return '';
		}
	}
	public static function getDescription($schemeID)
	{
		if(!is_numeric($schemeID))
		{
			return '';
		}

		$schemeID = (int)$schemeID;
		$descriptions = self::getAllDescriptions();
		return isset($descriptions[$schemeID]) ? $descriptions[$schemeID] : '';
	}
	/**
	* @return array Array of strings
	*/
	public static function getAllDescriptions()
	{
		if(!self::$allDescriptions[LANGUAGE_ID])
		{
			Main\Localization\Loc::loadMessages(__FILE__);
			self::$allDescriptions[LANGUAGE_ID] = array(
				self::DEAL_CONTACT_COMPANY => GetMessage('CRM_LEAD_CONV_DEAL_CONTACT_COMPANY'),
				self::DEAL_CONTACT => GetMessage('CRM_LEAD_CONV_DEAL_CONTACT'),
				self::DEAL_COMPANY => GetMessage('CRM_LEAD_CONV_DEAL_COMPANY'),
				self::DEAL => GetMessage('CRM_LEAD_CONV_DEAL'),
				self::CONTACT_COMPANY => GetMessage('CRM_LEAD_CONV_CONTACT_COMPANY'),
				self::CONTACT => GetMessage('CRM_LEAD_CONV_CONTACT'),
				self::COMPANY => GetMessage('CRM_LEAD_CONV_COMPANY'),
			);
		}
		return self::$allDescriptions[LANGUAGE_ID];
	}
	/**
	* @return array Array of strings
	*/
	public static function getJavaScriptDescriptions($checkPermissions = false)
	{
		$result = array();
		$descriptions = self::getAllDescriptions();

		if(!$checkPermissions)
		{
			$isDealPermitted = true;
			$isContactPermitted = true;
			$isCompanyPermitted = true;
		}
		else
		{
			$flags = array();
			\CCrmLead::PrepareConversionPermissionFlags(0, $flags);
			$isDealPermitted = $flags['CAN_CONVERT_TO_DEAL'];
			$isContactPermitted = $flags['CAN_CONVERT_TO_CONTACT'];
			$isCompanyPermitted = $flags['CAN_CONVERT_TO_COMPANY'];
		}

		if($isDealPermitted && $isContactPermitted && $isCompanyPermitted)
		{
			foreach($descriptions as $schemeID => $description)
			{
				$result[self::resolveName($schemeID)] = $description;
			}
		}
		else
		{
			$schemes = array();
			if($isDealPermitted)
			{
				if($isContactPermitted && $isCompanyPermitted)
				{
					$schemes[] = self::DEAL_CONTACT_COMPANY;
				}
				if($isContactPermitted)
				{
					$schemes[] = self::DEAL_CONTACT;
				}
				if($isCompanyPermitted)
				{
					$schemes[] = self::DEAL_COMPANY;
				}
				$schemes[] = self::DEAL;
			}
			if($isContactPermitted && $isCompanyPermitted)
			{
				$schemes[] = self::CONTACT_COMPANY;
			}
			if($isContactPermitted)
			{
				$schemes[] = self::CONTACT;
			}
			if($isCompanyPermitted)
			{
				$schemes[] = self::COMPANY;
			}

			foreach($schemes as $schemeID)
			{
				$result[self::resolveName($schemeID)] = $descriptions[$schemeID];
			}
		}
		return $result;
	}
}