<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2015 Thurly
 */
namespace Thurly\Main\UI;

class AdminPageNavigation extends PageNavigation
{
	protected $pageSizes = array(10, 20, 50, 100, 200, 500);
	protected $allowAll = true;

	/**
	 * @param string $id Navigation identity like "nav-cars".
	 */
	public function __construct($id)
	{
		parent::__construct($id);
		$this->initFromUri();
	}
}
