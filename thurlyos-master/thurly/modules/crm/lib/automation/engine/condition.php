<?php
namespace Thurly\Crm\Automation\Engine;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Condition
{
	const TYPE_FIELD = 'field';
	//const TYPE_VARIABLE = 'variable'; //reserved

	private $type;
	private $field;
	private $condition;
	private $value;

	public function __construct(array $params = null)
	{
		$this->setType(self::TYPE_FIELD);
		if ($params)
		{
			if (isset($params['field']))
			$this->setField($params['field']);
			if (isset($params['condition']))
			$this->setCondition($params['condition']);
			if (isset($params['value']))
			$this->setValue($params['value']);
		}
	}

	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * @param string $field
	 * @return Condition
	 */
	public function setField($field)
	{
		$this->field = (string)$field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * @param string $condition
	 * @return Condition
	 */
	public function setCondition($condition)
	{
		$this->condition = (string)$condition;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return Condition
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @param string $type
	 * @return Condition
	 */
	public function setType($type)
	{
		if ($type === static::TYPE_FIELD)
		{
			$this->type = $type;
		}
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	public function toArray()
	{
		return array(
			'type' => $this->getType(),
			'field' => $this->getField(),
			'condition' => $this->getCondition(),
			'value' => $this->getValue(),
		);
	}

	public function createBizprocActivity(array $childActivity)
	{
		$title = Loc::getMessage('CRM_AUTOMATION_CONDITION_TITLE');
		$activity = array(
			'Type' => 'IfElseActivity',
			'Name' => Robot::generateName(),
			'Properties' => array('Title' => $title),
			'Children' => array(
				array(
					'Type' => 'IfElseBranchActivity',
					'Name' => Robot::generateName(),
					'Properties' => array(
						'Title' => $title,
						'fieldcondition' => array(
							array(
								$this->getField(),
								$this->getCondition(),
								$this->getValue()
							)
						)
					),
					'Children' => array($childActivity)
				),
				array(
					'Type' => 'IfElseBranchActivity',
					'Name' => Robot::generateName(),
					'Properties' => array(
						'Title' => $title,
						'truecondition' => '1',
					),
					'Children' => array()
				)
			)
		);

		return $activity;
	}

	/**
	 * @param array $activity
	 * @return false|Condition
	 */
	public static function convertBizprocActivity(array &$activity)
	{
		$condition = false;
		if (
			count($activity['Children']) === 2
			&& $activity['Children'][0]['Type'] === 'IfElseBranchActivity'
			&& $activity['Children'][1]['Type'] === 'IfElseBranchActivity'
			&& !empty($activity['Children'][0]['Properties']['fieldcondition'])
			&& !empty($activity['Children'][1]['Properties']['truecondition'])
			&& count($activity['Children'][0]['Children']) === 1
			&& count($activity['Children'][0]['Properties']['fieldcondition']) === 1
		)
		{
			$fieldCondition = $activity['Children'][0]['Properties']['fieldcondition'][0];
			$condition = new static(array(
				'field' => $fieldCondition[0],
				'condition' => $fieldCondition[1],
				'value' => $fieldCondition[2],
			));
			$activity = $activity['Children'][0]['Children'][0];
		}

		return $condition;
	}
}