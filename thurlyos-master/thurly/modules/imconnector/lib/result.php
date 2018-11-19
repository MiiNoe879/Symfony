<?php
namespace Thurly\ImConnector;

class Result extends \Thurly\Main\Result
{
	/**
	 * Sets only the result.
	 * @param $result
	 */
	public function setResult($result)
	{
		$this->data = array('RESULT' => $result);
	}

	/**
	 * To return a single result
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->data['RESULT'];
	}
}