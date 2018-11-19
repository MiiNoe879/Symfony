<?php
namespace Thurly\Crm\Conversion;
use Thurly\Main;
class LeadConversionConfig extends EntityConversionConfig
{
	const OPTION_NAME = 'crm_lead_conversion';

	public function __construct(array $params = null)
	{
		$this->addItem(new EntityConversionConfigItem(\CCrmOwnerType::Contact));
		$this->addItem(new EntityConversionConfigItem(\CCrmOwnerType::Company));
		$this->addItem(new EntityConversionConfigItem(\CCrmOwnerType::Deal));
	}

	public static function getDefault()
	{
		$config = new LeadConversionConfig();
		foreach(array(\CCrmOwnerType::Contact, \CCrmOwnerType::Company) as $typeID)
		{
			$item = $config->getItem($typeID);
			$item->setActive(true);
			$item->enableSynchronization(true);
		}
		return $config;
	}

	public static function load()
	{
		$s = Main\Config\Option::get('crm', static::OPTION_NAME, '', '');
		$params = $s !== '' ? unserialize($s) : null;
		if(!is_array($params))
		{
			return null;
		}

		$item = new static();
		$item->internalize($params);
		return $item;
	}

	function save()
	{
		Main\Config\Option::set('crm', static::OPTION_NAME, serialize($this->externalize()), '');
	}

	public function getSchemeID()
	{
		$contactConfig = $this->getItem(\CCrmOwnerType::Contact);
		$companyConfig = $this->getItem(\CCrmOwnerType::Company);
		$dealConfig = $this->getItem(\CCrmOwnerType::Deal);
		if($dealConfig->isActive() && $contactConfig->isActive() && $companyConfig->isActive())
		{
			return LeadConversionScheme::DEAL_CONTACT_COMPANY;
		}
		elseif($dealConfig->isActive() && $contactConfig->isActive())
		{
			return LeadConversionScheme::DEAL_CONTACT;
		}
		elseif($dealConfig->isActive() && $companyConfig->isActive())
		{
			return LeadConversionScheme::DEAL_COMPANY;
		}
		elseif($dealConfig->isActive())
		{
			return LeadConversionScheme::DEAL;
		}
		elseif($contactConfig->isActive() && $companyConfig->isActive())
		{
			return LeadConversionScheme::CONTACT_COMPANY;
		}
		elseif($contactConfig->isActive())
		{
			return LeadConversionScheme::CONTACT;
		}
		elseif($companyConfig->isActive())
		{
			return LeadConversionScheme::COMPANY;
		}
		return LeadConversionScheme::UNDEFINED;
	}

	public static function getCurrentSchemeID()
	{
		$config = self::load();
		if($config === null)
		{
			$config = self::getDefault();
		}

		$schemeID = $config->getSchemeID();
		if($schemeID === LeadConversionScheme::UNDEFINED)
		{
			$schemeID = LeadConversionScheme::getDefault();
		}

		return $schemeID;
	}
}