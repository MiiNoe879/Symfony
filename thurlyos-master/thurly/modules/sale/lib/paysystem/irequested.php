<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Main\Request;

interface IRequested
{
	/**
	 * @param Request $request
	 * @return ServiceResult
	 */
	public function createMovementListRequest(Request $request);

	/**
	 * @param $requestId
	 * @return ServiceResult
	 */
	public function getMovementListStatus($requestId = null);

	/**
	 * @param $requestId
	 * @return ServiceResult
	 */
	public function getMovementList($requestId = null);
}