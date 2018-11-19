<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sender
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Sender;


class SenderConnectorSubscriber extends \Thurly\Sender\Connector
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Email-marketing - Subscriber';
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return "sender_subscriber";
	}
	/** @return \CDBResult */
	public function getData()
	{
		$mailingId = $this->getFieldValue('MAILING_ID', 0);

		$mailingDb = MailingSubscriptionTable::getSubscriptionList(array(
			'select' => array('NAME' => 'CONTACT.NAME', 'EMAIL' => 'CONTACT.EMAIL', 'USER_ID' => 'CONTACT.USER_ID'),
			'filter' => array(
				'MAILING_ID' => $mailingId,
			)
		));

		return new \CDBResult($mailingDb);
	}

	/**
	 * @return string
	 */
	public function getForm()
	{
		return '';
	}
}
