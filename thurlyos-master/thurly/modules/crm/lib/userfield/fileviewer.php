<?php
namespace Thurly\Crm\UserField;
use Thurly\Main;
class FileViewer
{
	protected static $urlTemplates = array(
		\CCrmOwnerType::LeadName => "/thurly/components/thurly/crm.lead.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#",
		\CCrmOwnerType::ContactName => "/thurly/components/thurly/crm.contact.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#",
		\CCrmOwnerType::CompanyName => "/thurly/components/thurly/crm.company.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#",
		\CCrmOwnerType::DealName => "/thurly/components/thurly/crm.deal.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#",
		\CCrmOwnerType::Invoice => "/thurly/components/thurly/crm.invoice.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#",
		\CCrmOwnerType::Quote => "/thurly/components/thurly/crm.quote.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#"
	);

	/** @var int */
	protected $entityTypeID = 0;
	/** @var string */
	protected $entityTypeName = '';

	public function __construct($entityTypeID)
	{
		$this->entityTypeID = $entityTypeID;
		$this->entityTypeName = \CCrmOwnerType::ResolveName($entityTypeID);
	}

	public function getUrl($entityID, $fieldName, $fileID = 0)
	{
		$params = array('owner_id' => $entityID, 'field_name' => $fieldName);
		if($fileID > 0)
		{
			$params['file_id'] = $fileID;
		}
		return \CComponentEngine::MakePathFromTemplate(self::$urlTemplates[$this->entityTypeName], $params);
	}
}