<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seo
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Seo;

interface IEngine
{
	public function getCode();

	public function getInterface();

	public function getAuthSettings();

	public function setAuthSettings($settings);
}