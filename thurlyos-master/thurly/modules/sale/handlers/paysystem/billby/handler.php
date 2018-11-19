<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Loader;
use Thurly\Sale;
use Thurly\Sale\PaySystem;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));

class BillByHandler extends BillHandler
{

}