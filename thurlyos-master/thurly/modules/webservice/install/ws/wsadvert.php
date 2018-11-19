<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Title");
?><?$APPLICATION->IncludeComponent(
	"thurly:webservice.server",
	"",
	Array(
		"WEBSERVICE_NAME" => "thurly.wsdl.test1", 
		"WEBSERVICE_MODULE" => "webservice", 
		"WEBSERVICE_CLASS" => "CGenericWSDLTestWS"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>