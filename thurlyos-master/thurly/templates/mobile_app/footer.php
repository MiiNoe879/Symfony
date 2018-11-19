<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
} ?>

<script>
	<?if ($APPLICATION->GetPageProperty("LAZY_AUTOLOAD", true) === true):?>
	document.addEventListener("deviceready", function ()
	{
		if(typeof window.ThurlyMobile !== "undefined")
			ThurlyMobile.LazyLoad.showImages();
	}, false);
	<?endif?>

	<?if ($APPLICATION->GetPageProperty("LAZY_AUTOSCROLL", true) === true):?>
	document.addEventListener("DOMContentLoaded", function ()
	{
		if(typeof window.ThurlyMobile !== "undefined")
		{
			window.addEventListener("scroll", ThurlyMobile.LazyLoad.onScroll, { passive: true });
		}
	}, false);
	<?endif?>


	document.addEventListener('DOMContentLoaded', function ()
	{
		BX.bindDelegate(document.body, 'click', {tagName: 'A'}, function (e)
		{
			if(this.hostname == document.location.hostname)
			{
				var params = BX.MobileTools.getMobileUrlParams(this.href);
				if (params)
				{
					BXMobileApp.PageManager.loadPageBlank(params);
					return BX.PreventDefault(e);
				}
			}
		});
	}, false);


</script>
</body>
</html>