<?php
namespace Thurly\Crm\Conversion;
use Thurly\Crm\Synchronization\UserFieldSynchronizer;
use Thurly\Main;
abstract class EntityConverter
{
	/** @var EntityConversionConfig */
	protected $config = null;
	/** @var int */
	protected $entityID = 0;
	/** @var int */
	protected $currentPhase = 0;
	/** @var array */
	protected $contextData = array();
	/** @var array */
	protected $resultData = array();
	/** @var \CCrmPerms|null  */
	protected $userPermissions = null;

	/**
	 * @param EntityConversionConfig $config
	 */
	public function __construct(EntityConversionConfig $config)
	{
		$this->config = $config;
	}
	/**
	 * Initialize converter.
	 * @return void
	 */
	public function initialize()
	{
	}
	//region Access to member fields
	/**
	 * Get converter entity type ID.
	 * @return int
	 */
	abstract public function getEntityTypeID();
	/**
	 * Get converter entity ID.
	 * @return int
	 */
	public function getEntityID()
	{
		return $this->entityID;
	}
	/**
	 * Set converter entity ID.
	 * @param int $entityID Entity ID.
	 * @return void
	 */
	public function setEntityID($entityID)
	{
		$this->entityID = $entityID;
	}
	/**
	 * Get current converter phase.
	 * @return int
	 */
	public function getCurrentPhase()
	{
		return $this->currentPhase;
	}
	/**
	 * Check if current phase is final.
	 * @return bool
	 */
	public function isFinished()
	{
		return false;
	}
	public function getEntityConfig($entityTypeID)
	{
		return $this->config->getItem($entityTypeID);
	}

	/**
	 * Get converter context data.
	 * @return array
	 */
	public function getContextData()
	{
		return $this->contextData;
	}
	/**
	 * Set converter context data.
	 * @param array $contextData Converter context data.
	 * @return void
	 */
	public function setContextData(array $contextData)
	{
		$this->contextData = $contextData;
	}
	/**
	 * Get converter result data.
	 * @return array
	 */
	public function getResultData()
	{
		return $this->resultData;
	}
	/**
	 * Get current user ID.
	 * @return int
	 */
	public function getUserID()
	{
		return \CCrmSecurityHelper::GetCurrentUserID();
	}
	/**
	 * Get current user permissions
	 * @return \CCrmPerms
	 */
	protected function getUserPermissions()
	{
		if($this->userPermissions === null)
		{
			$this->userPermissions = \CCrmPerms::GetCurrentUserPermissions();
		}

		return $this->userPermissions;
	}
	/**
	 * Get converter configuration
	 * @return EntityConversionConfig
	 */
	public function getConfig()
	{
		return $this->config;
	}
	//endregion
	/**
	 * Try to execute current conversion phase.
	 * @return bool
	 */
	abstract public function executePhase();
	/**
	 * Map entity fields to specified type.
	 * @param int $entityTypeID Entity type ID.
	 * @param array|null $options Mapping options.
	 * @return array
	 */
	abstract public function mapEntityFields($entityTypeID, array $options = null);
	//region Externalization/Internalization
	/**
	 * Externalize converter settings
	 * @return array
	 */
	public function externalize()
	{
		return array(
			'config' => $this->config->externalize(),
			'entityId' => $this->entityID,
			'currentPhase' => $this->currentPhase,
			'resultData' => $this->resultData
		);
	}
	/**
	 * Internalize converter settings.
	 * @param array $params Income parameters.
	 * @return void
	 */
	public function internalize(array $params)
	{
		if(isset($params['config']) && is_array($params['config']))
		{
			$this->config->internalize($params['config']);
		}

		if(isset($params['entityId']))
		{
			$this->entityID = (int)$params['entityId'];
		}

		if(isset($params['currentPhase']))
		{
			$this->currentPhase = (int)$params['currentPhase'];
		}

		if(isset($params['resultData']) && is_array($params['resultData']))
		{
			$this->resultData = $params['resultData'];
		}
	}
	//endregion
	//region Misc.
	/**
	 * Get Supported Destination Types
	 * @return array
	 */
	public function getSupportedDestinationTypeIDs()
	{
		return array();
	}
	/**
	 * Get deal category IDs are allowed to use in converter
	 * @param \CCrmPerms|null $permissions
	 * @return array
	 */
	public static function getPermittedDealCategoryIDs(\CCrmPerms $permissions = null)
	{
		return \CAllCrmDeal::GetPermittedToCreateCategoryIDs($permissions);
	}

	public static function getDestinationEntityID($entityTypeName, array $data)
	{
		return isset($data[$entityTypeName]) ? (int)$data[$entityTypeName] : 0;
	}

	public static function setDestinationEntityID($entityTypeName, $entityID, array &$data, array $options = null)
	{
		if(!is_int($entityID))
		{
			$entityID = (int)$entityID;
		}

		if(!is_array($options))
		{
			$options = array();
		}

		$data[$entityTypeName] = $entityID;
		if(isset($options['isNew']) && $options['isNew'])
		{
			$data["IS_RECENT_{$entityTypeName}"] = true;
		}
	}

	public static function isNewDestinationEntity($entityTypeName, $entityID, array $data)
	{
		$isNewKeyName = "IS_RECENT_{$entityTypeName}";
		return ($entityID > 0
			&& isset($data[$entityTypeName])
			&& $data[$entityTypeName] == $entityID
			&& isset($data[$isNewKeyName]) && $data[$isNewKeyName]
		);
	}

	//endregion
	//region Permissions
	/**
	 * Check permission for CREATE operation.
	 * @param string $entityTypeName EntityTypeName.
	 * @param EntityConversionConfigItem $config Entity configuration.
	 * @return bool
	 */
	protected function checkCreatePermission($entityTypeName, EntityConversionConfigItem $config = null)
	{
		if(!$this->config->isPermissionCheckEnabled())
		{
			return true;
		}

		/** @var \CCrmPerms $permissions */
		$permissions = $this->getUserPermissions();

		if($entityTypeName === \CCrmOwnerType::CompanyName)
		{
			return \CCrmCompany::CheckCreatePermission($permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::ContactName)
		{
			return \CCrmContact::CheckCreatePermission($permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::DealName)
		{
			$initData = $config !== null ? $config->getInitData() : null;
			$categoryID = is_array($initData) && isset($initData['categoryId'])
				? max((int)$initData['categoryId'], 0) : 0;
			return \CCrmDeal::CheckCreatePermission($permissions, $categoryID);
		}
		elseif($entityTypeName === \CCrmOwnerType::InvoiceName)
		{
			return \CCrmInvoice::CheckCreatePermission($permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::QuoteName)
		{
			return \CCrmQuote::CheckCreatePermission($permissions);
		}

		return \CCrmAuthorizationHelper::CheckCreatePermission($entityTypeName, $permissions);
	}
	/**
	 * Check permission for READ operation.
	 * @param string $entityTypeName EntityTypeName.
	 * @param int $entityID Entity ID.
	 * @return bool
	 */
	protected function checkReadPermission($entityTypeName, $entityID)
	{
		if(!$this->config->isPermissionCheckEnabled())
		{
			return true;
		}

		/** @var \CCrmPerms $permissions */
		$permissions = $this->getUserPermissions();

		if($entityTypeName === \CCrmOwnerType::CompanyName)
		{
			return \CCrmCompany::CheckReadPermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::ContactName)
		{
			return \CCrmContact::CheckReadPermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::DealName)
		{
			return \CCrmDeal::CheckReadPermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::InvoiceName)
		{
			return \CCrmInvoice::CheckReadPermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::QuoteName)
		{
			return \CCrmQuote::CheckReadPermission($entityID, $permissions);
		}

		return \CCrmAuthorizationHelper::CheckReadPermission($entityTypeName, $entityID, $permissions);
	}
	/**
	 * Check permission for UPDATE operation.
	 * @param string $entityTypeName EntityTypeName.
	 * @param int $entityID Entity ID.
	 * @return bool
	 */
	protected function checkUpdatePermission($entityTypeName, $entityID)
	{
		if(!$this->config->isPermissionCheckEnabled())
		{
			return true;
		}

		/** @var \CCrmPerms $permissions */
		$permissions = $this->getUserPermissions();

		if($entityTypeName === \CCrmOwnerType::CompanyName)
		{
			return \CCrmCompany::CheckUpdatePermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::ContactName)
		{
			return \CCrmContact::CheckUpdatePermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::DealName)
		{
			return \CCrmDeal::CheckUpdatePermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::InvoiceName)
		{
			return \CCrmInvoice::CheckUpdatePermission($entityID, $permissions);
		}
		elseif($entityTypeName === \CCrmOwnerType::QuoteName)
		{
			return \CCrmQuote::CheckUpdatePermission($entityID, $permissions);
		}

		return \CCrmAuthorizationHelper::CheckUpdatePermission($entityTypeName, $entityID, $permissions);
	}
	//endregion
}