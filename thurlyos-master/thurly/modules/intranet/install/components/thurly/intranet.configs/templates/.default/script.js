BX.namespace("BX.ThurlyOS.Configs");

BX.ThurlyOS.Configs.LogoClass = (function()
{
	var LogoClass = function(ajax_path)
	{
		this.ajaxPath = ajax_path;
	};

	LogoClass.prototype.LogoChange = function()
	{
		BX('config-wait').style.display='inline-block';
		BX.ajax.submit(
			BX('configLogoPostForm'),
			function(reply)
			{
				try {
					var json = JSON.parse(reply);

					if (json.error)
					{
						BX('config_logo_error_block').style.display = 'block';
						var error_block = BX.findChild(BX('config_logo_error_block'), {class: 'content-edit-form-notice-text'}, true, false);
						error_block.innerHTML = '<span class=\'content-edit-form-notice-icon\'></span>'+json.error;
					}
					else if (json.path)
					{
						BX('config_logo_error_block').style.display = 'none';
						BX('logo_24_text').style.display = 'none';
						BX('logo_24_img').src = json.path;
						BX('logo_24_img').style.display = 'block';
						BX('config_logo_img').src = json.path;
						BX('config_logo_img_div').style.display = 'inline-block';
						BX('config_logo_delete_link').style.display = 'inline-block';
					}
					BX('config-wait').style.display='none';
				} catch (e) {
					BX('config-wait').style.display='none';
					return false;
				}
			}
		);
	};

	LogoClass.prototype.LogoDelete = function(curLink)
	{
		if (confirm(BX.message("LogoDeleteConfirm")))
		{
			BX('config-wait').style.display='inline-block';

			BX.ajax.post(
				this.ajaxPath,
				{
					client_delete_logo:'Y',
					sessid : BX.thurly_sessid()
				},
				function(){
					BX('logo_24_img').src = '';
					BX('logo_24_img').style.display = 'none';
					BX('logo_24_text').style.display = 'block';
					BX('config_logo_img_div').style.display = 'none';
					curLink.style.display = 'none';
					BX('config_error_block').style.display = 'none';
					BX('config-wait').style.display='none';
				}
			);
		}
	};

	return LogoClass;
})();

BX.ThurlyOS.Configs.LiveFeedRightClass = (function()
{
	var LiveFeedRightClass = function(arToAllRights)
	{
		this.arToAllRights = arToAllRights;
	};

	LiveFeedRightClass.prototype.DeleteToAllAccessRow = function(ob)
	{
		var divNode = BX('RIGHTS_div', true);
		var div = BX.findParent(ob, {tag: 'div', className: 'toall-right'}, divNode);
		if (div)
			var right = div.getAttribute('data-bx-right');

		if (div && right)
		{
			BX.remove(div);
			var artoAllRightsNew = [];

			for(var i = 0; i < this.arToAllRights.length; i++)
				if (this.arToAllRights[i] != right)
					artoAllRightsNew[artoAllRightsNew.length] = this.arToAllRights[i];

			this.arToAllRights = BX.clone(artoAllRightsNew);

			var hidden_el = BX('livefeed_toall_rights_' + right);
			if (hidden_el)
				BX.remove(hidden_el);
		}
	};

	LiveFeedRightClass.prototype.ShowToAllAccessPopup = function(val)
	{
		var curObj = this;
		val = val || [];

		BX.Access.Init({
			other: {
				disabled: false,
				disabled_g2: true,
				disabled_cr: true
			},
			groups: { disabled: true },
			socnetgroups: { disabled: true }
		});

		var startValue = {};
		for(var i = 0; i < val.length; i++)
			startValue[val[i]] = true;

		BX.Access.SetSelected(startValue);

		BX.Access.ShowForm({
			callback: function(arRights)
			{
				var divNode = BX('RIGHTS_div', true);
				var pr = false;

				for(var provider in arRights)
				{
					pr = BX.Access.GetProviderName(provider);
					for(var right in arRights[provider])
					{
						divNode.appendChild(BX.create('div', {
							attrs: {
								'data-bx-right': right
							},
							props: {
								'className': 'toall-right'
							},
							children: [
								BX.create('span', {
									html: (pr.length > 0 ? pr + ': ' : '') + arRights[provider][right].name + '&nbsp;'
								}),
								BX.create('a', {
									attrs: {
										href: 'javascript:void(0);',
										title: BX.message('SLToAllDel')
									},
									props: {
										'className': 'access-delete'
									},
									events: {
										click: function() { curObj.DeleteToAllAccessRow(this); }
									}
								})
							]
						}));

						BX('configPostForm').appendChild(BX.create('input', {
							attrs: {
								'type': 'hidden'
							},
							props: {
								'name': 'livefeed_toall_rights[]',
								'id': 'livefeed_toall_rights_' + right,
								'value': right
							}
						}));

						curObj.arToAllRights[curObj.arToAllRights.length] = arRights[provider][right].id;
					}
				}
			}
		});
	};

	return LiveFeedRightClass;
})();

BX.ThurlyOS.Configs.ImGeneralChatClass = (function()
{
	var ImGeneralChatClass = function(arToAllRights)
	{
		this.arToAllRights = arToAllRights;
	};

	ImGeneralChatClass.prototype.DeleteToAllAccessRow = function(ob)
	{
		var divNode = BX('chat_RIGHTS_div', true);
		var div = BX.findParent(ob, {tag: 'div', className: 'toall-right'}, divNode);
		if (div)
			var right = div.getAttribute('data-bx-right');

		if (div && right)
		{
			BX.remove(div);
			var artoAllRightsNew = [];

			for(var i = 0; i < this.arToAllRights.length; i++)
				if (this.arToAllRights[i] != right)
					artoAllRightsNew[artoAllRightsNew.length] = this.arToAllRights[i];

			this.arToAllRights = BX.clone(artoAllRightsNew);

			var hidden_el = BX('imchat_toall_rights_' + right);
			if (hidden_el)
				BX.remove(hidden_el);
		}
	};

	ImGeneralChatClass.prototype.ShowToAllAccessPopup = function(val)
	{
		var curObj = this;
		val = val || [];

		BX.Access.Init({
			other: {
				disabled: false,
				disabled_g2: true,
				disabled_cr: true
			},
			groups: { disabled: true },
			socnetgroups: { disabled: true }
		});

		var startValue = {};
		for(var i = 0; i < val.length; i++)
			startValue[val[i]] = true;

		BX.Access.SetSelected(startValue);

		BX.Access.ShowForm({
			callback: function(arRights)
			{
				var divNode = BX('chat_RIGHTS_div', true);
				var pr = false;

				for(var provider in arRights)
				{
					pr = BX.Access.GetProviderName(provider);
					for(var right in arRights[provider])
					{
						divNode.appendChild(BX.create('div', {
							attrs: {
								'data-bx-right': right
							},
							props: {
								'className': 'toall-right'
							},
							children: [
								BX.create('span', {
									html: (pr.length > 0 ? pr + ': ' : '') + arRights[provider][right].name + '&nbsp;'
								}),
								BX.create('a', {
									attrs: {
										href: 'javascript:void(0);',
										title: BX.message('SLToAllDel')
									},
									props: {
										'className': 'access-delete'
									},
									events: {
										click: function() { curObj.DeleteToAllAccessRow(this); }
									}
								})
							]
						}));

						BX('configPostForm').appendChild(BX.create('input', {
							attrs: {
								'type': 'hidden'
							},
							props: {
								'name': 'imchat_toall_rights[]',
								'id': 'imchat_toall_rights_' + right,
								'value': right
							}
						}));

						curObj.arToAllRights[curObj.arToAllRights.length] = arRights[provider][right].id;
					}
				}
			}
		});
	};

	return ImGeneralChatClass;
})();

BX.ThurlyOS.Configs.IpSettingsClass = (function()
{
	var IpSettingsClass = function(arCurIpRights)
	{
		this.arCurIpRights = arCurIpRights;
	};

	IpSettingsClass.prototype.DeleteIpAccessRow = function(ob)
	{
		var tdObj = ob.parentNode.parentNode;
		BX.remove(ob.parentNode);
		var allInputBlocks = BX.findChildren(tdObj, {tagName:'div'}, true);
		if (allInputBlocks.length <= 0)
		{
			var deleteRight = tdObj.parentNode.getAttribute("data-bx-right");
			var arCurIpRightsNew = [];
			for(var i = 0; i < this.arCurIpRights.length; i++)
				if (this.arCurIpRights[i] != deleteRight)
					arCurIpRightsNew.push(this.arCurIpRights[i]);
			this.arCurIpRights = arCurIpRightsNew;

			BX.remove(tdObj.parentNode);
		}
	};

	IpSettingsClass.prototype.ShowIpAccessPopup = function(val)
	{
		var curObj = this;

		val = val || [];

		BX.Access.Init({
			other: {
				disabled: false,
				disabled_g2: true,
				disabled_cr: true
			},
			groups: { disabled: true },
			socnetgroups: { disabled: true }
		});

		var startValue = {};
		for(var i = 0; i < val.length; i++)
			startValue[val[i]] = true;

		BX.Access.SetSelected(startValue);

		BX.Access.ShowForm({
			callback: function(arRights)
			{
				var pr = false;

				for(var provider in arRights)
				{
					pr = BX.Access.GetProviderName(provider);
					for(var right in arRights[provider])
					{
						var insertBlock = BX.create('tr', {
							attrs: {
								"data-bx-right" : right
							},
							children: [
								BX.create('td', {
									html: (pr.length > 0 ? pr + ': ' : '') + arRights[provider][right].name + '&nbsp;',
									props: {
										'className': 'content-edit-form-field-name'
									}
								}),
								BX.create('td', {
									props: {
										'className': 'content-edit-form-field-input',
										'colspan': 2
									},
									children: [
										BX.create('div', {
											children: [
												BX.create('input', {
													attrs: {
														type: 'text',
														name: 'ip_access_rights_' + right+'[]',
														size: '30'
													},
													props: {
													},
													events: {
														click: function() {
															curObj.addInputForIp(this);
														}
													}
												}),
												BX.create('a', {
													attrs: {
														href: 'javascript:void(0);',
														title: BX.message('SLToAllDel')
													},
													props: {
														'className': 'access-delete'
													},
													events: {
														click: function() { curObj.DeleteIpAccessRow(this); }
													}
												})
											]
										})
									]
								})
							]
						});

						BX('ip_add_right_button').parentNode.insertBefore(insertBlock, BX('ip_add_right_button'));

						curObj.arCurIpRights.push(right);
					}
				}
			}
		});
	};

	IpSettingsClass.prototype.addInputForIp = function(input)
	{
		var curObj = this;

		var inputParent = input.parentNode;
		if (BX.nextSibling(inputParent))
			return;

		var newInputBlock = BX.clone(inputParent);
		var newInput = BX.firstChild(newInputBlock);
		newInput.value = "";
		newInput.onclick = function(){curObj.addInputForIp(this)};
		BX.nextSibling(newInput).onclick = function(){curObj.DeleteIpAccessRow(this)};
		inputParent.parentNode.appendChild(newInputBlock);
	};

	return IpSettingsClass;
})();

BX.ThurlyOS.Configs.Functions = {
	init : function ()
	{
		var toAllCheckBox = BX('allow_livefeed_toall');
		var defaultCont = BX('DEFAULT_all');

		if (toAllCheckBox && defaultCont)
		{
			BX.bind(toAllCheckBox, 'click', BX.delegate(function(e) {
				defaultCont.style.display = (this.checked ? "" : "none");
			}, toAllCheckBox));
		}

		var rightsCont = BX('RIGHTS_all');
		if (toAllCheckBox && rightsCont)
		{
			BX.bind(toAllCheckBox, 'click', BX.delegate(function(e) {
				rightsCont.style.display = (this.checked ? "" : "none");
			}, toAllCheckBox));
		}

		if (BX("configLogoPostForm") && BX("configLogoPostForm").client_logo)
		{
			BX.bind(BX("configLogoPostForm").client_logo, "change", function(){
				B24ConfigsLogo.LogoChange();
			});
		}

		if (BX("config_logo_delete_link"))
		{
			BX.bind(BX("config_logo_delete_link"), "click", function(){
				B24ConfigsLogo.LogoDelete(this);
			});
		}

		//im chat
		var toChatAllCheckBox = BX('allow_general_chat_toall');
		var chatRightsCont = BX('chat_rights_all');
		if (toChatAllCheckBox && chatRightsCont)
		{
			BX.bind(toChatAllCheckBox, 'click', function() {
				chatRightsCont.style.display = (this.checked ? "" : "none");
			});
		}

		if (BX.type.isDomNode((BX("smtp_use_auth"))))
		{
			BX.bind(BX("smtp_use_auth"), "change", BX.proxy(function ()
			{
				this.showHideSmtpAuth();
			}, this));
		}
	},

	submitForm : function (button)
	{
		BX.addClass(button, 'webform-button-wait webform-button-active');
		BX.submit(BX('configPostForm'));
	},
	
	otpSwitchOffInfo : function(elem)
	{
		if (!elem.checked)
		{
			BX.PopupWindowManager.create("otpSwitchOffInfo", elem, {
				autoHide: true,
				offsetLeft: -100,
				offsetTop: 15,
				overlay : false,
				draggable: {restrict:true},
				closeByEsc: true,
				closeIcon: { right : "12px", top : "10px"},
				content: '<div style="padding: 15px; width: 300px; font-size: 13px">' + BX.message("CONFIG_OTP_SECURITY_SWITCH_OFF_INFO") + '</div>'
			}).show();
		}
	},

	adminOtpIsRequiredInfo : function(elem)
	{
		BX.PopupWindowManager.create("adminOtpIsRequiredInfo", elem, {
			autoHide: true,
			offsetLeft: -100,
			offsetTop: 15,
			overlay : false,
			draggable: {restrict:true},
			closeByEsc: true,
			closeIcon: { right : "12px", top : "10px"},
			content: '<div style="padding: 15px; width: 300px; font-size: 13px">' + BX.message("CONFIG_OTP_ADMIN_IS_REQUIRED_INFO") + '</div>'
		}).show();
	}
};