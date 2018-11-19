<?
#############################################
# Thurly Site Manager Forum					#
# Copyright (c) 2002-2013 Thurly			#
# http://www.thurlysoft.com					#
# mailto:admin@thurlysoft.com				#
#############################################
IncludeModuleLangFile(__FILE__);

class CVoteNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return array(
			"vote" => array(
				"voting" => Array(
					"NAME" => GetMessage('V_VOTING'),
				)
			)
		);
	}
}
?>