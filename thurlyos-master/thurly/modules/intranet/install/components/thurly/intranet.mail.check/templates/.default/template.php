<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<? $frame = $this->createFrame()->begin(''); ?>
<? if ($arParams['SETTED_UP'] !== false): ?>
<script type="text/javascript">

	var ExternalMail = {
		interval: <?=max(intval($arParams['CHECK_INTERVAL']), 10) ?>
	};

	ExternalMail.check = function(force)
	{
		BX.ajax({
			url: "/thurly/tools/check_mail.php?SITE_ID=<?=SITE_ID; ?>",
			dataType: "json",
			lsId: "sync-mailbox",
			lsTimeout: ExternalMail.interval,
			lsForce: force ? true : false,
			onsuccess: function(json)
			{
				if (typeof json != "object")
					return;

				if (json.last_sync > 0)
					ExternalMail.scheduleSync(ExternalMail.interval);

				if (typeof BXIM == "object" && typeof BXIM.notify == "object" && typeof BXIM.notify.counters == "object")
					BXIM.notify.counters.mail_unseen = json.unseen;

				var b24MenuItem = BX('bx_left_menu_menu_external_mail') || BX('menu_external_mail');
				if (b24MenuItem)
				{
					var link = BX.findChild(b24MenuItem, {'class': 'menu-item-link'}, true);
					if (typeof link == "object")
						BX.adjust(link, {props: {target: json.last_check >= 0 ? "_blank" : "_self"}});

					if (typeof B24 == "object" && typeof B24.updateCounters == "function")
						B24.updateCounters({mail_unseen: json.unseen});
				}
				else if (BX("menu_extmail_counter"))
				{
					var link    = BX("menu_extmail_counter");
					var counter = BX.findChild(link, {"class": "user-indicator-text"}, true);
					var warning = BX("menu_extmail_warning");

					if (typeof counter == "object")
						BX.adjust(counter, {text: json.unseen});

					if (typeof link == "object")
						BX.adjust(link, {style: {display: json.result == "ok" ? "inline-block" : "none"}});
					if (typeof warning == "object")
						BX.adjust(warning, {style: {display: json.result == "ok" ? "none" : "inline-block"}});
				}

				if (typeof BXIM == "object" && typeof BXIM.notify == "object" && typeof BXIM.notify.updateNotifyMailCount == "function")
					BXIM.notify.updateNotifyMailCount(json.unseen);
			}
		});
	};

	ExternalMail.scheduleSync = function(timeout)
	{
		ExternalMail.syncTimeout = clearTimeout(ExternalMail.syncTimeout);
		ExternalMail.syncTimeout = setTimeout(ExternalMail.check, timeout*1000);
	};

	ExternalMail.scheduleStop = function()
	{
		ExternalMail.stopTimeout = clearTimeout(ExternalMail.stopTimeout);
		ExternalMail.stopTimeout = setTimeout(function() {
			ExternalMail.syncTimeout = clearTimeout(ExternalMail.syncTimeout);
		}, 15*60000);
	};

	<? if (intval($arParams['LAST_MAIL_CHECK']) >= 0): ?>

	BX.ready(function()
	{
		var b24MenuItem = BX('bx_left_menu_menu_external_mail') || BX('menu_external_mail');
		if (b24MenuItem)
			var link = BX.findChild(b24MenuItem, {'class': 'menu-item-link'}, true);
		else if (BX("menu_extmail_counter"))
			var link = BX("menu_extmail_counter");

		if (typeof link == "object")
			BX.adjust(link, {props: {target: "_blank"}});

		BX.bind(link, "click", function()
		{
			window.onfocus = function()
			{
				window.onfocus = null;
				ExternalMail.check(true);
			};
			return true;
		});

		<? if ($arParams['IS_TIME_TO_MAIL_CHECK']): ?>

		ExternalMail.check();

		<? elseif (intval($arParams['LAST_MAIL_SYNC']) >= 0): ?>

		ExternalMail.scheduleSync(<?=max(intval($arParams['LAST_MAIL_SYNC'])+intval($arParams['CHECK_INTERVAL'])-time(), 0) ?>);

		<? endif ?>
	});

	<? endif ?>

</script>

<? endif ?>
<? $frame->end(); ?>