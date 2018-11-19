<?php
namespace Thurly\Crm\Settings;

use Thurly\Main;

class Setting
{
	/** @var string */
	protected $name = '';
	function __construct($name)
	{
		$this->name = $name;
	}

	public function remove()
	{
		Main\Config\Option::delete('crm', array('name' => $this->name));
	}
}
