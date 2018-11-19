<?php

namespace Thurly\Mobile;

class Auth
{
	public static function setNotAuthorizedHeaders()
	{
		header("HTTP/1.0 401 Not Authorized");
		header('WWW-Authenticate: Basic realm="ThurlyOS"');
		header("Content-Type: application/x-javascript");
		header("BX-Authorize: ".thurly_sessid());
	}

	public static function getNotAuthorizedResponse()
	{
		return Array(
			"status" => "failed",
			"thurly_sessid"=>thurly_sessid()
		);
	}


}