<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Request;
use Thurly\Sale;
use Thurly\Sale\PaySystem;
use Thurly\Main\Loader;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));
Loc::loadMessages(__FILE__);

class BillUaHandler extends BillHandler
{
	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();
		$data['SELLER_COMPANY_MFO'] = '300012';
		$data['SELLER_COMPANY_IPN'] = '12547856696';
		$data['SELLER_COMPANY_EDRPOY'] = '19017842';
		$data['SELLER_COMPANY_PDV'] = '20154519017842';
		$data['SELLER_COMPANY_SYS'] = Loc::getMessage('SALE_HPS_BILL_UA_COMPANY_SYS');;
		$data['BILLUA_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_UA_COMMENT');
		$data['BILLUA_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_UA_COMMENT_ADD');

		return $data;
	}

}