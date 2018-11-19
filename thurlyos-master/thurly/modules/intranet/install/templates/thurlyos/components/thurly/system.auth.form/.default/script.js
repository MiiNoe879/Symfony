BX.namespace("BX.Intranet.SystemAuthForm");

BX.Intranet.SystemAuthForm =
{
	licenseHandler: function(params)
	{
		if (typeof params !== "object")
			return;

		var url = params.COUNTER_URL || "",
			licensePath = params.LICENSE_PATH || "",
			host = params.HOST || "";

		BX.ajax.post(
			url,
			{
				action: "upgradeButton",
				host: host
			},
			BX.proxy(function(){
				document.location.href = licensePath;
			}, this)
		);
	},

	showUserInfoSpotLight: function(ajaxPath)
	{
		var userBlock = BX("user-block");
		var node = BX("user-name") ? BX("user-name") : userBlock;

		var spotlight = new BX.SpotLight({
			targetElement: node,
			targetVertex: "middle-center",
			left: 4,
			lightMode: true,
			events: {
				onTargetEnter: function ()
				{
					spotlight.close();

					var popup = new BX.PopupWindow("menu-custom-preset-delete-popup", node, {
						closeIcon: true,
						contentColor: "white",
						angle: true,
						width: 300,
						offsetTop: 15,
						content: BX("userSpotLightForm"),
						buttons: [
							(button = new BX.PopupWindowButton({
								text: BX.message("AUTH_SAVE_BUTTON"),
								className: "popup-window-button-create",
								events: {
									click: BX.proxy(function ()
									{
										BX.addClass(button.buttonNode, "popup-window-button-wait");

										var data = {
											"firstName": BX("spotlightFirstName").value,
											"lastName": BX("spotlightLastName").value,
											"photo": BX.type.isDomNode(document.forms["userSpotLightForm"].elements["spotlightPhotoId"]) ? document.forms["userSpotLightForm"].elements["spotlightPhotoId"].value : ""
										};

										BX.ajax({
											method: 'POST',
											dataType: 'json',
											url: ajaxPath,
											data: {
												sessid: BX.thurly_sessid(),
												action: "saveUserData",
												data: data
											},
											onsuccess: BX.proxy(function (json)
											{
												document.location.reload();
												/*if (json.hasOwnProperty("error"))
												{
													alert(json.error);
												}
												else
												{

												}*/
											}, this),
											onfailure: function ()
											{
											}
										});
									}, this)
								}
							})),
							/*new BX.PopupWindowButton({
								text: BX.message("AUTH_DELAY_BUTTON"),
								className: "popup-window-button-link ",
								events: {
									click: function ()
									{
										this.popupWindow.close();

										BX.ajax({
											method: 'POST',
											dataType: 'json',
											url: ajaxPath,
											data: {
												sessid: BX.thurly_sessid(),
												action: "delayUserSpotLight"
											},
											onsuccess: BX.proxy(function ()
											{
											}, this),
											onfailure: function ()
											{
											}
										});
									}
								}
							})*/
						],
						events : {
							onPopupClose : BX.proxy(function() {
								BX.ajax({
									method: 'POST',
									dataType: 'json',
									url: ajaxPath,
									data: {
										sessid: BX.thurly_sessid(),
										action: "delayUserSpotLight"
									},
									onsuccess: BX.proxy(function ()
									{
									}, this),
									onfailure: function ()
									{
									}
								});
							}, this)
						}
					});

					popup.show();
				}
			}
		});
		spotlight.show();
	}
};