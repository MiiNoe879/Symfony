<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/extranet/contacts/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("MENU_CONTACT"),
		"/extranet/contacts/index.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("MENU_EMPLOYEE"),
		"/extranet/contacts/employees.php",
		Array(),
		Array(),
		""
	)
);
?>