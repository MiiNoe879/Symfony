<?php
namespace Thurly\Crm\Search;
use Thurly\Crm\DealTable;
use Thurly\Crm\Binding\DealContactTable;
class DealSearchContentBuilder extends SearchContentBuilder
{
	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Deal;
	}
	public function isFullTextSearchEnabled()
	{
		return DealTable::getEntity()->fullTextIndexEnabled('SEARCH_CONTENT');
	}
	protected function prepareEntityFields($entityID)
	{
		$dbResult = \CCrmDeal::GetListEx(
			array(),
			array('=ID' => $entityID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			array('*'/*, 'UF_*'*/)
		);

		$fields = $dbResult->Fetch();
		return is_array($fields) ? $fields : null;
	}
	public function prepareEntityFilter(array $params)
	{
		$value = isset($params['SEARCH_CONTENT']) ? $params['SEARCH_CONTENT'] : '';
		if(!is_string($value) || $value === '')
		{
			return array();
		}

		$operation = $this->isFullTextSearchEnabled() ? '*' : '*%';
		return array("{$operation}SEARCH_CONTENT" => SearchEnvironment::prepareToken($value));
	}

	/**
	 * Prepare search map.
	 * @param array $fields Entity Fields.
	 * @return SearchMap
	 */
	protected function prepareSearchMap(array $fields)
	{
		$map = new SearchMap();

		$entityID = isset($fields['ID']) ? (int)$fields['ID'] : 0;
		if($entityID <= 0)
		{
			return $map;
		}

		$map->add($entityID);
		$map->addField($fields, 'ID');
		$map->addField($fields, 'TITLE');

		$map->addField($fields, 'OPPORTUNITY');
		$map->add(
			\CCrmCurrency::GetCurrencyName(
				isset($fields['CURRENCY_ID']) ? $fields['CURRENCY_ID'] : ''
			)
		);

		if(isset($fields['ASSIGNED_BY_ID']))
		{
			$map->addUserByID($fields['ASSIGNED_BY_ID']);
		}

		//region Company
		$companyID = isset($fields['COMPANY_ID']) ? (int)$fields['COMPANY_ID'] : 0;
		if($companyID > 0)
		{
			$map->add(
				\CCrmOwnerType::GetCaption(\CCrmOwnerType::Company, $companyID, false)
			);

			$map->addEntityMultiFields(
				\CCrmOwnerType::Company,
				$companyID,
				array(\CCrmFieldMulti::PHONE, \CCrmFieldMulti::EMAIL)
			);
		}
		//endregion

		//region Contacts
		$contactIDs = DealContactTable::getDealContactIDs($entityID);
		foreach($contactIDs as $contactID)
		{
			$map->add(
				\CCrmOwnerType::GetCaption(\CCrmOwnerType::Contact, $contactID, false)
			);

			$map->addEntityMultiFields(
				\CCrmOwnerType::Contact,
				$contactID,
				array(\CCrmFieldMulti::PHONE, \CCrmFieldMulti::EMAIL)
			);
		}
		//endregion

		return $map;
	}
	protected function save($entityID, SearchMap $map)
	{
		DealTable::update($entityID, array('SEARCH_CONTENT' => $map->getString()));
	}
}