<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

if (IsModuleInstalled("thurlyos"))
{
	GetGlobalID();
}

/*
$arPostProperty = array("UF_BLOG_POST_DOC");
if (IsModuleInstalled("webdav"))
{
	$arPostProperty[] = "UF_BLOG_POST_FILE";
}
*/
$APPLICATION->IncludeComponent("thurly:main.post.form", "mobile_comment", array(
		"FORM_ID" => "commentEditForm",
		"COMMENT_TYPE" => $_REQUEST["type"],
		"COMMENT_ID" => intval($_REQUEST["comment_id"]),
		"NODE_ID" => $_REQUEST["node_id"]
	),
	false,
	Array("HIDE_ICONS" => "Y")
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>