<?php

namespace Thurly\Sale\Exchange;


use Thurly\Main\ArgumentException;
use Thurly\Main\Error;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Exchange\Entity\EntityImport;
use Thurly\Sale\Exchange\Entity\ShipmentImport;
use Thurly\Sale\Exchange\Entity\UserProfileImport;
use Thurly\Sale\Exchange\OneC\DocumentImport;
use Thurly\Sale\Internals\Fields;
use Thurly\Sale\Result;

abstract class ImportOneCBase extends ImportPattern
{
	const DELIVERY_SERVICE_XMLID = 'ORDER_DELIVERY';

	/** @var  Fields */
	protected $fields;

	/**
	 * @param array $values
	 * @internal param array $fields
	 */
	public function setFields(array $values)
	{
		foreach ($values as $key=>$value)
		{
			$this->setField($key, $value);
		}
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setField($name, $value)
	{
		$this->fields->set($name, $value);
	}

	/**
	 * @param $name
	 * @return null|string
	 */
	public function getField($name)
	{
		return $this->fields->get($name);
	}

	/**
	 * @param array $items
	 * @return Result
	 */
	protected function checkFields(array $items)
	{
		$result = new Result();

		foreach($items as $item)
		{
			$params = $item->getFieldValues();
			$fields = $params['TRAITS'];

			if(strlen($fields[$item::getFieldExternalId()])<= 0)
				$result->addErrors(array(new Error(" ".EntityType::getDescription($item->getOwnerTypeId()).": ".GetMessage("SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND"), 'SALE_EXCHANGE_EXTERNAL_ID_NOT_FOUND')));
		}

		return $result;
	}

	/**
	 * @return Result
	 */
	static public function checkSettings()
	{
		return new Result();
	}

	/**
	 * @param Entity\EntityImport|ProfileImport $item
	 * @return Result
	 * @throws ArgumentException
	 * @internal
	 */
	protected function modifyEntity($item)
	{
		$result = new Result();

		if(!($item instanceof EntityImport) && !($item instanceof UserProfileImport))
			throw new ArgumentException("Item must be instanceof EntityImport or UserProfileImport");

		$params = $item->getFieldValues();

		$fieldsCriterion = $fields = &$params['TRAITS'];

		$converter = OneC\Converter::getInstance($item->getOwnerTypeId());
		$converter->loadSettings($item->getSettings());

		/** @var OneC\Converter $converter*/
		$converter->sanitizeFields($item->getEntity(), $fields);
		$item->refreshData($fields);

		$criterion = $item->getCurrentCriterion($item->getEntity());
		$collision = $item->getCurrentCollision($item->getOwnerTypeId());

		if($item instanceof ShipmentImport)
			$fieldsCriterion['ITEMS'] = $params['ITEMS'];

		if($criterion->equals($fieldsCriterion))
		{
			$collision->resolve($item);
		}

		if(!$criterion->equals($fieldsCriterion) ||
			($criterion->equals($fieldsCriterion) && !$item->hasCollisionErrors()))
		{
			$result = $item->import($params);
		}

		return $result;
	}

	/**
	 * @param array $rawFields
	 * @return Result
	 * @throws \Thurly\Main\ArgumentOutOfRangeException
	 * @throws \Thurly\Main\NotSupportedException
	 */
	public function parse(array $rawFields)
	{
		$result = new Result();
		$list = array();

		foreach($rawFields as $raw)
		{
			$documentTypeId = $this->resolveDocumentTypeId($raw);

			$document = OneC\DocumentImportFactory::create($documentTypeId);

			$fields = $document::prepareFieldsData($raw);

			$document->setFields($fields);

			$list[] = $document;
		}

		$result->setData($list);

		return $result;
	}

	/**
	 * @param OneC\DocumentImport $document
	 * @return Entity\OrderImport|Entity\PaymentCardImport|Entity\PaymentCashImport|Entity\PaymentCashLessImport|ShipmentImport|UserProfileImport|ProfileImport
	 */
	protected function convertDocument(DocumentImport $document)
	{
		$settings = Manager::getSettingsByType($document->getOwnerEntityTypeId());

		$convertor = OneC\Converter::getInstance($document->getOwnerEntityTypeId());
		$convertor->loadSettings($settings);
		$fields = $convertor->resolveParams($document);

		$loader = Entity\EntityImportLoaderFactory::create($document->getOwnerEntityTypeId());
		$loader->loadSettings($settings);

		if(strlen($document->getId())>0)
			$fieldsEntity = $loader->getByNumber($document->getId());
		else
			$fieldsEntity = $loader->getByExternalId($document->getExternalId());

		if(!empty($fieldsEntity['ID']))
			$fields['TRAITS']['ID'] = $fieldsEntity['ID'];

		$entityImport = Manager::createImport($document->getOwnerEntityTypeId());
		$entityImport->setFields($fields);

		return $entityImport;
	}

	/**
	 * @param array $fields
	 * @return int
	 */
	protected function resolveDocumentTypeId(array $fields)
	{
		return OneC\DocumentImport::resolveDocumentTypeId($fields);
	}

	/**
	 * @return array
	 */
	protected static function getMessage()
	{
		return Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"].'/thurly/components/thurly/sale.export.1c/component.php');
	}
}