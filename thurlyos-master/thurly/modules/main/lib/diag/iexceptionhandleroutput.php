<?php
namespace Thurly\Main\Diag;

interface IExceptionHandlerOutput
{
	/**
	 * @param \Error|\Exception $exception
	 * @param bool $debug
	 */
	public function renderExceptionMessage($exception, $debug = false);
}
