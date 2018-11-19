<?
IncludeModuleLangFile(__FILE__);

class CForumNotifySchema
{
	public function __construct()
	{
	}

	public static function OnGetNotifySchema()
	{
		return IsModuleInstalled('thurlyos')? array(): array(
			"forum" => array(
				"comment" => Array(
					"NAME" => GetMessage("FORUM_NS_COMMENT"),
				),
/*
				"mention" => Array(
					"NAME" => GetMessage("FORUM_NS_MENTION"),
				),
*/
			),
		);
	}
}