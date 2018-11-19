<?require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?>

<?$APPLICATION->IncludeComponent("thurly:mobile.calendar.help","", 
	Array(
		"EVENT_ID" => $_REQUEST['event_id']
	),false
);?>


<?require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');?>