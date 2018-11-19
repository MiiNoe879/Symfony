<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Localization\Loc;
use Thurly\Sale;
use Thurly\Sale\PaySystem;
use Thurly\Main\Loader;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));
Loc::loadMessages(__FILE__);

class BillEnHandler extends BillHandler
{
	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();
		$data['BILLEN_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT');
		$data['BILLEN_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_EN_COMMENT_ADD');

		return $data;
	}

}