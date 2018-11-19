<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sender
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SenderEventHandler
{
	/**
	 * @param $data
	 * @return mixed
	 */
	public static function onConnectorListUser($data)
	{
		$data['CONNECTOR'] = 'Thurly\Main\SenderConnectorUser';

		return $data;
	}
}


class SenderConnectorUser extends \Thurly\Sender\Connector
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Loc::getMessage('sender_connector_user_name');
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return "user";
	}

	/** @return \CDBResult */
	public function getData()
	{
		$groupId = $this->getFieldValue('GROUP_ID', null);
		$dateRegister = $this->getFieldValue('DATE_REGISTER', null);
		$active = $this->getFieldValue('ACTIVE', null);

		$filter = array();
		if($groupId)
			$filter['GROUP_ID'] = $groupId;

		if(strlen($dateRegister)>0)
		{
			if(\Thurly\Main\Type\Date::isCorrect($dateRegister))
			{
				$dateRegister = new \Thurly\Main\Type\Date($dateRegister);
				$filter['><USER.DATE_REGISTER'] = array($dateRegister->toString(), $dateRegister->add('1 DAY')->toString());
			}
			else
			{
				$result = new \CDBResult();
				$result->InitFromArray(array());
				return $result;
			}
		}

		if($active=='Y')
			$filter['USER.ACTIVE'] = $active;
		elseif($active=='N')
			$filter['USER.ACTIVE'] = $active;

		$userDb = \Thurly\Main\UserGroupTable::getList(array(
			'select' => array('NAME' => 'USER.NAME', 'EMAIL' => 'USER.EMAIL', 'USER_ID'),
			'filter' => $filter,
			'group' => array('NAME', 'EMAIL', 'USER_ID'),
			'order' => array('USER_ID' => 'ASC'),
		));

		return new \CDBResult($userDb);
	}

	/**
	 * @return string
	 * @throws ArgumentException
	 */
	public function getForm()
	{
		$groupInput = '<select name="'.$this->getFieldName('GROUP_ID').'">';
		$groupDb = \Thurly\Main\GroupTable::getList(array(
			'select' => array('ID', 'NAME',),
			'filter' => array('!=ID' => 2),
			'order' => array('C_SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while($group = $groupDb->fetch())
		{
			$inputSelected = ($group['ID'] == $this->getFieldValue('GROUP_ID') ? 'selected' : '');
			$groupInput .= '<option value="'.$group['ID'].'" '.$inputSelected.'>';
			$groupInput .= htmlspecialcharsbx($group['NAME']);
			$groupInput .= '</option>';
		}
		$groupInput .= '</select>';


		$booleanValues = array(
			'' => Loc::getMessage('sender_connector_user_all'),
			'Y' => Loc::getMessage('sender_connector_user_y'),
			'N' => Loc::getMessage('sender_connector_user_n'),
		);

		$activeInput = '<select name="'.$this->getFieldName('ACTIVE').'">';
		foreach($booleanValues as $k => $v)
		{
			$inputSelected = ($k == $this->getFieldValue('ACTIVE') ? 'selected' : '');
			$activeInput .= '<option value="'.$k.'" '.$inputSelected.'>';
			$activeInput .= htmlspecialcharsbx($v);
			$activeInput .= '</option>';
		}
		$activeInput .= '</select>';


		$dateRegInput = CalendarDate(
			$this->getFieldName('DATE_REGISTER'),
			$this->getFieldValue('DATE_REGISTER'),
			$this->getFieldFormName()
		);

		return '
			<table>
				<tr>
					<td>'.Loc::getMessage('sender_connector_user_group').'</td>
					<td>'.$groupInput.'</td>
				</tr>
				<tr>
					<td>'.Loc::getMessage('sender_connector_user_datereg').'</td>
					<td>'.$dateRegInput.'</td>
				</tr>
				<tr>
					<td>'.Loc::getMessage('sender_connector_user_active').'</td>
					<td>'.$activeInput.'</td>
				</tr>
			</table>
		';
	}
}
