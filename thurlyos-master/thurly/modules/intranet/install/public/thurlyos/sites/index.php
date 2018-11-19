<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

if (IsModuleInstalled("landing"))
{
	$APPLICATION->IncludeComponent(
		"thurly:landing.start",
		".default",
		array(
			"COMPONENT_TEMPLATE" => ".default",
			"SEF_FOLDER" => "/sites/",
			"SEF_MODE" => "Y",
			"SEF_URL_TEMPLATES" => array(
				"sites" => "",
				"site_show" => "site/#site_show#/",
				"site_edit" => "site/edit/#site_edit#/",
				"landing_edit" => "site/#site_show#/#landing_edit#/",
				"domains" => "domains/",
				"domain_edit" => "domain/edit/#domain_edit#/",
			)
		),
		false
	);
	?>
	<script>
		BX.ready(function ()
		{
			var pageTitle = BX("pagetitle");

			if (BX.type.isDomNode(pageTitle))
            {
				pageTitle.appendChild(BX.create("span", {
					attrs: {className: "pagetitle-item-beta"},
					html: "beta"
				}));
            }
		});
	</script>
	<?
}
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>