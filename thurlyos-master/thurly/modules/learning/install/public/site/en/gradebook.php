<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent(
	"thurly:learning.student.gradebook",
	"",
	Array(
		"TEST_DETAIL_TEMPLATE" => "course/index.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID#", 
		"COURSE_DETAIL_TEMPLATE" => "course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y", 
		"TEST_ID_VARIABLE" => "TEST_ID", 
		"SET_TITLE" => "Y" 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>