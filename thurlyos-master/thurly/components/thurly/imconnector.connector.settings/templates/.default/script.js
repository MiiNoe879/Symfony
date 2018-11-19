;(function(window){
	var destInput = function(id, node, inputName)
	{
		this.node = node;
		this.id = id;
		this.inputName = inputName;
		this.node.appendChild(BX.create('SPAN', {
			props : { className : "bx-destination-wrap" },
			html : [
				'<span id="', this.id, '-container"><span class="bx-destination-wrap-item"></span></span>',
				'<span class="bx-destination-input-box" id="', this.id, '-input-box">',
					'<input type="text" value="" class="bx-destination-input" id="', this.id, '-input">',
				'</span>',
				'<a href="#" class="bx-destination-add" id="', this.id, '-add-button"></a>'
			].join('')}));
		BX.defer_proxy(this.bind, this)();
	};
	destInput.prototype = {
		bind : function()
		{
			this.nodes = {
				inputBox : BX(this.id + '-input-box'),
				input : BX(this.id + '-input'),
				container : BX(this.id + '-container'),
				button : BX(this.id + '-add-button')
			};
			BX.bind(this.nodes.input, 'keyup', BX.proxy(this.search, this));
			BX.bind(this.nodes.input, 'keydown', BX.proxy(this.searchBefore, this));
			BX.bind(this.nodes.button, 'click', BX.proxy(function(e){BX.SocNetLogDestination.openDialog(this.id); BX.PreventDefault(e); }, this));
			BX.bind(this.nodes.container, 'click', BX.proxy(function(e){BX.SocNetLogDestination.openDialog(this.id); BX.PreventDefault(e); }, this));
			this.onChangeDestination();
			BX.addCustomEvent(this.node, 'select', BX.proxy(this.select, this));
			BX.addCustomEvent(this.node, 'unSelect', BX.proxy(this.unSelect, this));
			BX.addCustomEvent(this.node, 'delete', BX.proxy(this.delete, this));
			BX.addCustomEvent(this.node, 'openDialog', BX.proxy(this.openDialog, this));
			BX.addCustomEvent(this.node, 'closeDialog', BX.proxy(this.closeDialog, this));
			BX.addCustomEvent(this.node, 'closeSearch', BX.proxy(this.closeSearch, this));
		},
		select : function(item, el, prefix)
		{
			if (BX.message('LM_BUSINESS_USERS_ON') == 'Y' && BX.message('LM_BUSINESS_USERS').split(',').indexOf(item.id) == -1)
			{
				BX.SocNetLogDestination.closeDialog(this.id);
				imolOpenTrialPopup('imol_queue');
				return false;
			}
			if(!BX.findChild(this.nodes.container, { attr : { 'data-id' : item.id }}, false, false))
			{
				el.appendChild(BX.create("INPUT", { props : {
						type : "hidden",
						name : ('CONFIG['+this.inputName+']'+ '[' + prefix + '][]'),
						value : item.id
					}
				}));
				this.nodes.container.appendChild(el);
			}
			this.onChangeDestination();
		},
		unSelect : function(item)
		{
			var elements = BX.findChildren(this.nodes.container, {attribute: {'data-id': ''+item.id+''}}, true);
			if (elements !== null)
			{
				for (var j = 0; j < elements.length; j++)
					BX.remove(elements[j]);
			}
			this.onChangeDestination();
		},
		onChangeDestination : function()
		{
			this.nodes.input.innerHTML = '';
			this.nodes.button.innerHTML = (BX.SocNetLogDestination.getSelectedCount(this.id) <= 0 ? BX.message("LM_ADD1") : BX.message("LM_ADD2"));
		},
		openDialog : function()
		{
			BX.style(this.nodes.inputBox, 'display', 'inline-block');
			BX.style(this.nodes.button, 'display', 'none');
			BX.focus(this.nodes.input);
		},
		closeDialog : function()
		{
			if (this.nodes.input.value.length <= 0)
			{
				BX.style(this.nodes.inputBox, 'display', 'none');
				BX.style(this.nodes.button, 'display', 'inline-block');
				this.nodes.input.value = '';
			}
		},
		closeSearch : function()
		{
			if (this.nodes.input.value.length > 0)
			{
				BX.style(this.nodes.inputBox, 'display', 'none');
				BX.style(this.nodes.button, 'display', 'inline-block');
				this.nodes.input.value = '';
			}
		},
		searchBefore : function(event)
		{
			if (event.keyCode == 8 && this.nodes.input.value.length <= 0)
			{
				BX.SocNetLogDestination.sendEvent = false;
				BX.SocNetLogDestination.deleteLastItem(this.id);
			}
			return true;
		},
		search : function(event)
		{
			if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
				return false;

			if (event.keyCode == 13)
			{
				BX.SocNetLogDestination.selectFirstSearchItem(this.id);
				return true;
			}
			if (event.keyCode == 27)
			{
				this.nodes.input.value = '';
				BX.style(this.nodes.button, 'display', 'inline');
			}
			else
			{
				BX.SocNetLogDestination.search(this.nodes.input.value, true, this.id);
			}

			if (!BX.SocNetLogDestination.isOpenDialog() && this.nodes.input.value.length <= 0)
			{
				BX.SocNetLogDestination.openDialog(this.id);
			}
			else if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
			{
				BX.SocNetLogDestination.closeDialog();
			}
			if (event.keyCode == 8)
			{
				BX.SocNetLogDestination.sendEvent = true;
			}
			return true;
		}
	};

	window.BX.OpenLinesConfigEdit = {
		popupTooltip: {},
		addEventForTooltip : function()
		{
			var arNodes = BX.findChildrenByClassName(BX('imconnector-new'), "tel-context-help");
			for (var i = 0; i < arNodes.length; i++)
			{
				if (arNodes[i].getAttribute('context-help') == 'y')
					continue;

				arNodes[i].setAttribute('data-id', i);
				arNodes[i].setAttribute('context-help', 'y');
				BX.bind(arNodes[i], 'mouseover', function(){
					var id = this.getAttribute('data-id');
					var text = this.getAttribute('data-text');

					BX.OpenLinesConfigEdit.showTooltip(id, this, text);
				});
				BX.bind(arNodes[i], 'mouseout', function(){
					var id = this.getAttribute('data-id');
					BX.OpenLinesConfigEdit.hideTooltip(id);
				});
			}
		},
		showTooltip : function(id, bind, text)
		{
			if (this.popupTooltip[id])
				this.popupTooltip[id].close();

			this.popupTooltip[id] = new BX.PopupWindow('bx-imopenlines-tooltip', bind, {
				lightShadow: true,
				autoHide: false,
				darkMode: true,
				offsetLeft: 0,
				offsetTop: 2,
				bindOptions: {position: "top"},
				zIndex: 200,
				events : {
					onPopupClose : function() {this.destroy()}
				},
				content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
			});
			this.popupTooltip[id].setAngle({offset:13, position: 'bottom'});
			this.popupTooltip[id].show();

			return true;
		},
		hideTooltip : function(id)
		{
			this.popupTooltip[id].close();
			this.popupTooltip[id] = null;
		}
	};

	BX.ready(function(){
		BX.OpenLinesConfigEdit.addEventForTooltip();
	});



	BX.ImConnectorConnectorSettings = function(params)
	{
		this.ajaxUrl = '/thurly/components/thurly/imopenlines.lines/ajax.php';

		return this;
	};

	BX.ImConnectorConnectorSettings.prototype =
	 {
		 createLine: function (detailPageUrlTemplate)
		 {
			 if(this.isActiveControlLocked)
			 {
				 return;
			 }

			 this.isActiveControlLocked = true;
			 this.sendActionRequest(
				 'create',
				 function(data)
				 {
					 location.href = detailPageUrlTemplate.replace('#LINE#', data.config_id);
				 },
				 function(data)
				 {
					 data = data || {'error': true, 'text': ''};
					 this.isActiveControlLocked = false;

					 if(data.limited)
					 {
						 if(!B24 || !B24['licenseInfoPopup'])
						 {
							 return;
						 }

						 B24.licenseInfoPopup.show(
							 'crm_webform_activation',
							 BX.message('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TITLE'),
							 '<span>' + BX.message('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TEXT') + '</span>'
						 );
					 }
					 else
					 {
						 this.showErrorPopup(data);
					 }
				 }
			 );
		 },
		 sendActionRequest: function (action, callbackSuccess, callbackFailure)
		 {
			 callbackSuccess = callbackSuccess || null;
			 callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);

			 BX.ajax({
				 url: this.ajaxUrl,
				 method: 'POST',
				 data: {
					 'action': action,
					 'config_id': this.id,
					 'sessid': BX.thurly_sessid()
				 },
				 timeout: 30,
				 dataType: 'json',
				 processData: true,
				 onsuccess: BX.proxy(function(data){
					 data = data || {};
					 if(data.error)
					 {
						 callbackFailure.apply(this, [data]);
					 }
					 else if(callbackSuccess)
					 {
						 callbackSuccess.apply(this, [data]);
					 }
				 }, this),
				 onfailure: BX.proxy(function(){
					 var data = {'error': true, 'text': ''};
					 callbackFailure.apply(this, [data]);
				 }, this)
			 });
		 },
		 showErrorPopup: function (data)
		 {
			 data = data || {};
			 var text = data.text || BX.message('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_ERROR_ACTION');
			 var popup = BX.PopupWindowManager.create(
				 'crm_webform_list_error',
				 null,
				 {
					 autoHide: true,
					 lightShadow: true,
					 closeByEsc: true,
					 overlay: {backgroundColor: 'black', opacity: 500}
				 }
			 );
			 popup.setButtons([
				 new BX.PopupWindowButton({
					 text: BX.message('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CLOSE'),
					 events: {click: function(){this.popupWindow.close();}}
				 })
			 ]);
			 popup.setContent('<span class="crm-webform-edit-warning-popup-alert">' + text + '</span>');
			 popup.show();
		 }
	 }

})(window);