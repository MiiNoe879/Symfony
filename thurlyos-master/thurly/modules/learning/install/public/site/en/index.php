<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent(
	"thurly:learning.course.list",
	"",
	Array(
		"SORBY" => "SORT", 
		"SORORDER" => "ASC", 
		"COURSE_DETAIL_TEMPLATE" => "course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y", 
		"CHECK_PERMISSIONS" => "Y", 
		"COURSES_PER_PAGE" => "20", 
		"SET_TITLE" => "Y" 
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>