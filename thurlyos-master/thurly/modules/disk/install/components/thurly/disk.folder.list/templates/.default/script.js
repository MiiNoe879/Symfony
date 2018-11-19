BX.namespace("BX.Disk");
BX.Disk.FolderListClass = (function (){

	var FolderListClass = function (parameters)
	{
		this.errors = parameters.errors || [];
		this.information = parameters.information || '';

		this.currentFolder = parameters.currentFolder || {};
		this.grid = parameters.grid;
		this.infoPanelContainer = BX(parameters.infoPanelContainer);
		this.gridGroupActionButton = BX(parameters.gridGroupActionButton);
		this.gridShowTreeButton = BX(parameters.gridShowTreeButton);
		this.rootObject = parameters.rootObject || {};
		this.storage = parameters.storage || {};
		this.storage.manage = this.storage.manage || {};
		this.enabledModZip = parameters.enabledModZip || false;
		this.enabledExternalLink = parameters.enabledExternalLink;
		this.enabledObjectLock = parameters.enabledObjectLock;
		this.currentObjectIdInInfoPanel = null;
		this.getFilesCountAndSize = parameters.getFilesCountAndSize || {};
		this.containerWithExtAndIntLinks = null;
		this.cacheExternalLinks = {};

		this.actionGroupButton = parameters.actionGroupButton || 'move';
		this.grid.SetActionName(this.actionGroupButton);

		this.checkboxes = {};

		this.isThurlyOS = parameters.isThurlyOS || false;

		this.destFormName = parameters.destFormName || 'folder-list-destFormName';

		this.resetCheckboxes();
		this.ajaxUrl = '/thurly/components/thurly/disk.folder.list/ajax.php';

		this.setGroupActionTargetObjectId(this.rootObject.id);
		this.setEvents();
		this.initGroupActionButton();
		this.workWithLocationHash();

		if(this.errors.length)
			this.showErrors();
		if(this.information.length)
			this.showInformation();

		var items = BX.findChildren(this.grid.tableNode, {className: 'draggable', tagName: 'div'}, true);
		var gridClassName = this.grid.isTile? 'bx-disk-interface-tile' : 'bx-disk-interface-filelist';
		for (var i = 0; i < items.length; i++)
		{
			window.jsDD.registerObject(items[i]);

			items[i].onbxdragstart = function(){
				this._parent = this.parentNode;
				document.body.appendChild(
					BX.create('div', {
						props: {
							className: gridClassName
						},
						style: {
							paddingTop: 0,
							border: 'none'
						},
						children: [this]
					})
				);

				this.style.border = '1px solid #E8E8E8';
				this.style.top = '-1000px';
				this.style.left = '-1000px';
			};

			items[i].onbxdragrelease = function(){
				this._parent.appendChild(this);
				this.style.border = '';
				this.style.position = '';
				this.style.top = '';
				this.style.left = '';
				this.style.zIndex = '';
			};

			items[i].onbxdrag = function(x, y){
				this.style.position = 'absolute';
				this.style.zIndex = 9999999;

				this.style.left = x + 'px';
				this.style.top = y + 'px';
			};

			items[i].onbxdragfinish = BX.delegate(function(destination, x, y){
				var proxyContext = BX.proxy_context;
				var objectId = proxyContext.getAttribute('data-object-id');
				BX.remove(proxyContext._parent);
				BX.remove(proxyContext);
				try {
					this.removeRow(objectId);
				} catch (e) {}
			}, this);

			var icon = BX.findChild(items[i], {
				tagName: 'div',
				className: 'bx-file-icon-container-small'
			});

			if(!icon && BX.hasClass(items[i], 'bx-file-icon-container-small'))
			{
				icon = items[i];
			}

			if(icon && BX.hasClass(icon, 'bx-disk-folder-icon'))
			{
				window.jsDD.registerDest(items[i]);
				items[i].onbxdestdraghout = function ()
				{
					BX.removeClass(this, 'selected');
				};
				items[i].onbxdestdragfinish = BX.delegate(function (currentNode, x, y) {
					if(currentNode.getAttribute('data-object-id') == BX.proxy_context.getAttribute('data-object-id'))
					{
						return false;
					}
					BX.Disk.ajax({
						method: 'POST',
						dataType: 'json',
						url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'moveTo'),
						data: {
							objectId: currentNode.getAttribute('data-object-id'),
							targetObjectId: BX.proxy_context.getAttribute('data-object-id')
						},
						onsuccess: function (response) {
							BX.Disk.showModalWithStatusAction(response);
						}
					});

					return true;
				}, this);
				items[i].onbxdestdraghover = function (currentNode, x, y)
				{
					if(BX.hasClass(this, 'selected'))
					{
						return;
					}
					BX.addClass(this, 'selected');

					return true;
				};
			}
			else if(icon)
			{
				var lockIcon = BX.findChild(icon, {
					className: 'js-disk-locked-document-tooltip'
				}, false);

				if(lockIcon)
				{
					BX.tooltip(
						lockIcon.getAttribute('data-lock-created-by'),
						lockIcon.getAttribute('id')
					);
				}

			}
		}
	};

	FolderListClass.prototype.resetCheckboxes = function ()
	{
		var checkboxes = BX.findChildren(this.grid.tableNode, {tagName: 'input', attr: {type: 'checkbox'}}, true);
		for(var i in checkboxes)
		{
			if(!checkboxes.hasOwnProperty(i))
			{
				continue;
			}
			var checkbox = checkboxes[i];
			if(checkbox.getAttribute('name') === 'ID[]')
			{
				checkbox.checked = false;
			}
		}
	};

	FolderListClass.prototype.showInformation = function()
	{
		BX.Disk.showModalWithStatusAction({status: 'success', message: this.information});
	};

	FolderListClass.prototype.showErrors = function()
	{
		BX.Disk.showModalWithStatusAction({status: 'error', errors: this.errors});
	};

	FolderListClass.prototype.onKeyPress = function(e)
	{
		var charCode = e.which || e.keyCode;
		var charTyped = String.fromCharCode(charCode);
		var nameNodes = BX.findChildren(this.grid.tableNode, {
			tagName: 'a',
			className: 'bx-disk-folder-title'
		}, true);

		var byNames = Object.create(null);
		for(var i in nameNodes)
		{
			if(!nameNodes.hasOwnProperty(i))
				continue;
			var item = nameNodes[i];
			byNames[item.getAttribute('data-bx-title')] = item;
		}

		for(var name in byNames)
		{
			if(name.indexOf(charTyped) === 0)
			{
				var checkbox = this.getCheckbox(byNames[name].getAttribute('id').split('disk_obj_').pop());
				if(checkbox)
				{
					checkbox.checked = true;
					var row = this.getRowByCheckBox(checkbox);
					BX.addClass(row, 'active');
					BX.scrollToNode(row);
					this.grid.SelectRow(checkbox);
				}

				return;
			}
		}
	};

	FolderListClass.prototype.onHashChange = function()
	{
		var matches = document.location.hash.match(/hl-([0-9]+)/g);
		if(matches)
		{
			var command = (document.location.hash.match(/!([a-zA-Z]+)/g) || []).pop();
			for (var i in matches) {
				if (!matches.hasOwnProperty(i)) {
					continue;
				}
				var hl = matches[i];
				var number = hl.match(/hl-([0-9]+)/);
				if(number && number[1])
				{
					var checkbox = this.getCheckbox(number[1]);
					if(checkbox)
					{
						checkbox.checked = true;
						var row = this.getRowByCheckBox(checkbox);
						BX.addClass(row, 'active');
						BX.scrollToNode(row);
						this.grid.SelectRow(checkbox);
						this.runCommandOnObjectId(command, number[1]);
					}
					else if(command)
					{
						//we didn't find object on current page. May be it will be shown after reload :)
						if(window.BXIM && BXIM.isOpenNotify())
						{
							document.location.reload();
							BXIM.closeMessenger();
						}
					}
				}
			}

			if(window.BXIM && BXIM.isOpenNotify())
			{
				BXIM.closeMessenger();
			}
		}
	};

	FolderListClass.prototype.workWithLocationHash = function()
	{
		setTimeout(BX.delegate(function(){
			this.onHashChange();
		}, this), 350);
	};

	FolderListClass.prototype.setEvents = function()
	{
		BX.bind(BX('delete_button_' + this.grid.table_id), "click", BX.proxy(this.onClickDeleteGroup, this));
		BX.bind(this.gridGroupActionButton, 'click', BX.proxy(this.onClickGridGroupActionButton, this));
		BX.bind(this.gridShowTreeButton, 'click', BX.proxy(this.onClickGridShowTreeButton, this));
		BX.bind(this.getFilesCountAndSize.button, 'click', BX.proxy(this.onClickGetFilesCountAndSizeButtonButton, this));
		BX.bind(BX(this.storage.manage.connectButtonId), 'click', BX.proxy(this.onClickManageConnectButton, this));
		BX.bind(window, 'hashchange', BX.proxy(this.onHashChange, this));

		//BX.bind(window, 'keypress', BX.proxy(this.onKeyPress, this));


		//BX.bind(window, 'scroll', BX.proxy(this.onScroll, this));

		BX.addCustomEvent("onIframeElementLoadDataToView", BX.proxy(this.onIframeElementLoadDataToView, this));
		BX.addCustomEvent("onBeforeElementShow", BX.proxy(this.onBeforeElementShow, this));

		BX.addCustomEvent("onSelectRow", BX.proxy(this.onSelectRow, this));
		BX.addCustomEvent("onUnSelectRow", BX.proxy(this.onUnSelectRow, this));

		BX.addCustomEvent("onCreateExtendedFolder", BX.proxy(this.onCreateExtendedFolder, this));

		BX.addCustomEvent("onPullEvent-disk", BX.delegate(function(command, params) {
			params = params || {};
			switch (params.action)
			{
				case 'commit':
					if(!params.objectId)
					{
						break;
					}
					if(parseInt(params.contentVersion, 10) < 2)
					{
						break;
					}

					var objectData = BX.delegate(getObjectDataId, this)(params.objectId);

					var row = objectData.row;
					var title = objectData.title;
					if(!title)
					{
						break;
					}

					BX.Disk.showModalWithStatusAction({
						message: BX.message('DISK_FOLDER_LIST_LABEL_LIVE_UPDATE_FILE').replace('#NAME#', title.text),
						dontCloseCurrentPopupWindow: true
					});
					break;
			}
		}, this));
	};

	FolderListClass.prototype.initGroupActionButton = function()
	{
		var button = BX(this.gridGroupActionButton);
		if(!button)
		{
			return;
		}

		var mode = button.getAttribute('data-group-action');
		var label = BX.findChild(button, {tagName: 'span', className: 'js-text-group-action'});

		if(mode === 'move')
		{
			BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_MOVE_BUTTON')});
		}
		if(mode === 'copy')
		{
			BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_COPY_BUTTON')});
		}

		this.actionGroupButton = mode;
		this.grid.SetActionName(mode);
	};

	FolderListClass.prototype.runCommandOnObjectId = function(command, objectId)
	{
		if(!command)
		{
			return;
		}

		var objectData = BX.delegate(getObjectDataId, this)(objectId);

		var row = objectData.row;
		var title = objectData.title;
		var icon = objectData.icon;

		switch (command.toLowerCase())
		{
			case '!disconnect':
			case '!detach':
				this.openConfirmDetach({
					object: {
						id: objectId,
						name: title.text,
						isFolder: BX.hasClass(icon, 'bx-disk-folder-icon')
					}
				});
				break;
			case '!share':
				var actionData = getActionDataFromRowByPseudoName(this.getRow(objectId), 'share');
				if(actionData.ONCLICK)
				{
					if(actionData.ONCLICK.indexOf('showSharingDetailWithoutEdit') !== -1)
					{
						BX.Disk.showSharingDetailWithoutEdit({
							ajaxUrl: this.ajaxUrl,
							object: {
								id: objectId,
								name: title.text,
								isFolder: BX.hasClass(icon, 'bx-disk-folder-icon')
						}});
					}
					else if(actionData.ONCLICK.indexOf('showSharingDetailWithChangeRights') !== -1)
					{
						this.showSharingDetailWithChangeRights({
							object: {
								id: objectId,
								name: title.text,
								isFolder: BX.hasClass(icon, 'bx-disk-folder-icon')
						}});
					}
					else if(actionData.ONCLICK.indexOf('showSharingDetailWithSharing') !== -1)
					{
						this.showSharingDetailWithSharing({
							object: {
								id: objectId,
								name: title.text,
								isFolder: BX.hasClass(icon, 'bx-disk-folder-icon')
						}});
					}
				}
				break;
			case '!show':
				var linkWithObject = BX('disk_obj_' + objectId);
				if(!!linkWithObject) {
					BX.fireEvent(linkWithObject, 'click');
				}
				break;
			default:
				break;
		}
	};

	//todo create object which will describe folder/file.
	function getObjectDataId(objectId)
	{
		var row = this.getRow(objectId);
		return {
			row: this.getRow(objectId),
			title: BX.findChild(row, {
				tagName: 'a',
				className: 'bx-disk-folder-title'
			}, true),
			icon: BX.findChild(row, function(node){
				return BX.type.isElementNode(node) && (BX.hasClass(node, 'bx-disk-file-icon') || BX.hasClass(node, 'bx-disk-folder-icon'));
			}, true)
		}
	}

	function getIconElementByObjectId(objectId)
	{
		var row = this.getRow(objectId);
		var title = BX.findChild(row, {
			tagName: 'a',
			className: 'bx-disk-folder-title'
		}, true);
		return BX.findChild(row, function(node){
			return BX.type.isElementNode(node) && (BX.hasClass(node, 'bx-disk-file-icon') || BX.hasClass(node, 'bx-disk-folder-icon'));
		}, true);

	}

	FolderListClass.prototype.setGroupActionTargetObjectId = function (targetObjectId)
	{
		var pos = BX('grid_group_action_target_object');
		if(!pos)
		{
			this.grid.GetForm().appendChild(BX.create('input', {
				props: {
					id: 'grid_group_action_target_object',
					name: 'grid_group_action_target_object',
					type: 'hidden',
					value: targetObjectId
				}
			}));
		}
		else
		{
			pos.value = targetObjectId;
		}
	};

	FolderListClass.prototype.onClickManageConnectButton = function (e)
	{
		var target = BX.proxy_context;
		if(!BX.type.isDomNode(BX.proxy_context))
			return;
		var rootObjectId = this.storage.rootObject.id;
		if(BX.hasClass(target, 'connect'))
		{
			BX.Disk.ajax({
				method: 'POST',
				dataType: 'json',
				url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'connectToUserStorage'),
				data: {
					objectId: this.storage.rootObject.id
				},
				onsuccess: BX.delegate(function (response)
				{
					BX.Disk.showModalWithStatusAction(response);
					if(response.status != 'success')
					{
						return;
					}
					var icon = BX.findChild(target, {className: 'popup-current-icon'}, true);
					if(!!icon)
					{
						BX.removeClass(icon, 'webform-small-button-disk connect');
						BX.addClass(icon, 'webform-small-button-check-round disconnect');
					}
					var text = BX('bx-disk-disconnect-connect-disk-text');
					if(!!text)
					{
						BX.adjust(text, {text: BX.message('DISK_FOLDER_LIST_LABEL_ALREADY_CONNECT_DISK')});
					}
					BX.removeClass(target, 'webform-small-button-disk connect');
					BX.addClass(target, 'webform-small-button-check-round disconnect');

					if(!!response.manage.link)
					{
						this.storage.manage.link = BX.clone(response.manage.link, true);
					}

				}, this)
			});
			BX.PreventDefault(e);
		}
		else if(BX.hasClass(target, 'disconnect'))
		{
			this.openConfirmDetach({
				object: {
					id: this.storage.manage.link.object.id,
					name: this.storage.name,
					isFolder: true
				},
				onSuccess: function(response){
					if(response && response.status == 'success')
					{
						response.message = BX.message('DISK_FOLDER_LIST_LABEL_DISCONNECTED_DISK')
					}
					BX.Disk.showModalWithStatusAction(response);

					var icon = BX.findChild(target, {className: 'popup-current-icon'}, true);
					if(!!icon)
					{
						BX.removeClass(icon, 'webform-small-button-check-round disconnect');
						BX.addClass(icon, 'webform-small-button-disk connect');
					}
					var text = BX('bx-disk-disconnect-connect-disk-text');
					if(!!text)
					{
						BX.adjust(text, {text: BX.message('DISK_FOLDER_LIST_LABEL_CONNECT_DISK')});
					}
					BX.removeClass(target, 'webform-small-button-check-round disconnect');
					BX.addClass(target, 'webform-small-button-disk connect');
				}
			});
			BX.PreventDefault(e);
		}
	};

	FolderListClass.prototype.onClickGridShowTreeButton = function (e)
	{
		var targetObjectId = null;
		var targetObjectNode = null;
		BX.addCustomEvent("onSelectFolder", BX.delegate(function(node){
			if(!node.getAttribute('data-can-add'))
			{
				BX.removeClass(node, 'selected');
				return;
			}

			if(targetObjectNode)
			{
				BX.removeClass(targetObjectNode, 'selected');
			}
			targetObjectId = node.getAttribute('data-object-id');
			targetObjectNode = node;
			var td = BX.findChild(targetObjectNode, {className: 'bx-disk-wf-folder-name'}, true);
			if(td)
			{
				var label = BX.findChild(this.gridShowTreeButton, {tagName: 'span'});
				if(label)
				{
					var nodeWithName = BX.findChild(td, {tagName: 'span'}) || {};
					BX.adjust(label, {text: nodeWithName.textContent || nodeWithName.innerText});
				}
			}
			targetObjectId && this.setGroupActionTargetObjectId(targetObjectId);

			if(BX.PopupWindowManager.isPopupExists('bx-disk-toolbar-tree'))
			{
				if(BX.PopupWindowManager.getCurrentPopup())
				{
					BX.PopupWindowManager.getCurrentPopup().close();
				}
			}
		}, this));
		BX.addCustomEvent("onUnSelectFolder", function(node){
			targetObjectId = null;
			targetObjectNode = null;
			var pos = BX('grid_group_action_target_object');
			pos && BX.remove(pos);
		});

		var rootObject = this.rootObject;
		if(BX.PopupWindowManager.isPopupExists('bx-disk-toolbar-tree'))
		{
			BX.PopupWindowManager.create('bx-disk-toolbar-tree').show();
			return;
		}

		BX.Disk.ajax({
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSubFoldersToAdd'),
			method: 'POST',
			dataType: 'json',
			data: {
				objectId: rootObject.id
			},
			onsuccess: BX.delegate(function(response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				var rootNode = this.buildTreeNode(rootObject);
				var ul = BX.create('ul', {
					props: {
						className: 'bx-disk-wood-folder'
					}
				});
				rootNode.appendChild(ul);
				var bindElement = e.target ? e.target : (e.srcElement ? e.srcElement : null);
				if(bindElement)
				{
					bindElement = BX.findParent(bindElement, {className: 'popup-control', tagName: 'span'}, this.grid.tableNode) || bindElement;
				}
				this.buildTree(rootNode, response);
				BX.Disk.modalWindow({
					bindElement: bindElement,
					overlay: false,
					autoHide: true,
					modalId: 'bx-disk-toolbar-tree',
					events: {
						onPopupClose: function () {
							//this.destroy();
						}
					},
					content: [
						BX.create('ul', {
							props: {
								className: 'bx-disk-wood-folder'
							},
							children: [rootNode]
						})
					]
				});


			}, this)
		});
	};

	FolderListClass.prototype.onClickGetFilesCountAndSizeButtonButton = function (e)
	{
		BX.Disk.ajax({
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'calculateFileSizeAndCount'),
			method: 'POST',
			dataType: 'json',
			data: {
				folderId: this.currentFolder.id
			},
			onsuccess: BX.delegate(function(response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}

				BX.adjust(this.getFilesCountAndSize.sizeContainer, {text: response.size});
				BX.adjust(this.getFilesCountAndSize.countContainer, {text: response.count});

			}, this)
		});
	};

	FolderListClass.prototype.onClickGridGroupActionButton = function (e)
	{
		BX.PopupMenu.show(
			'folder-list-action-all-btn-menu',
			BX('folder-list-action-all-btn'),
			[
				(this.grid.getDeleteButton()?
					{
						text: BX.message('DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_MOVE_BUTTON'),
						onclick: BX.delegate(function (e) {
							var menu = BX.PopupMenu.getMenuById('folder-list-action-all-btn-menu');
							if(menu)
							{
								menu.bindElement.setAttribute('data-group-action', 'move');
								this.initGroupActionButton();
								menu.popupWindow.close();
							}
						}, this)
					} : null
				),
				{
					text: BX.message('DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_COPY_BUTTON'),
					onclick: BX.delegate(function (e) {
						var menu = BX.PopupMenu.getMenuById('folder-list-action-all-btn-menu');
						if(menu)
						{
							menu.bindElement.setAttribute('data-group-action', 'copy');
							this.initGroupActionButton();
							menu.popupWindow.close();
						}
					}, this)
				}
			],
			{
				autoHide: true
			}
		);
	};

	FolderListClass.prototype.onClickDeleteGroup = function(e)
	{
		if(!this.grid.IsActionEnabled())
			return false;
		var allRows = document.getElementById('actallrows_' + this.grid.table_id);

		this.openConfirmDeleteGroup({
			attemptDeleteAll: allRows && allRows.checked
		});
		BX.PreventDefault(e);
		return false;
	};

	FolderListClass.prototype.removeRow = function(objectId, completeCallback)
	{
		this.grid.removeRow(objectId, completeCallback);

		BX.onCustomEvent('onRemoveRowFromDiskList', [objectId]);
	};

	FolderListClass.prototype.getRow = function(objectId)
	{
		return this.grid.getRow(objectId);
	};

	FolderListClass.prototype.getActionButtonByPseudoName = function(objectId, pseudoName)
	{
		var actions = this.getActionButtons(objectId, true);

		for (var i in actions)
		{
			if (!actions.hasOwnProperty(i))
			{
				continue;
			}

			if(actions[i].getAttribute('data-pseudo-name') === pseudoName)
			{
				return actions[i];
			}
		}

		return null;
	};

	FolderListClass.prototype.getActionButtons = function(objectId, all)
	{
		all = all || false;

		var buttons = [];
		var actions = this.getActions(objectId);

		for (var i in actions) {
			if (!actions.hasOwnProperty(i)) {
				continue;
			}
			var action = actions[i];
			if(action && action['PSEUDO_NAME'])
			{
				var actionName = action['PSEUDO_NAME'].toLowerCase();
				switch(actionName)
				{
					case 'open':
						if(buttons.length > 0){
							buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
						}
						buttons.push(BX.create('a', {
							attrs: {onclick: action['ONCLICK']},
							text: action['TEXT'],
							style: {
								width: '145px'
							},
							props: {
								className: 'bx-disk-btn bx-disk-btn-medium bx-disk-btn-green'
							}
						}));
						break;
					case 'lock':
					case 'unlock':
					case 'share':
					case 'delete':
					case 'copy':
					case 'connect':

						if(actionName === 'lock' || actionName === 'unlock')
						{
							if(!this.enabledObjectLock)
							{
								break;
							}
							if(!action['SHOW'] && !all)
							{
								break;
							}
						}

						if(buttons.length > 0){
							buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
						}
						buttons.push(BX.create('a', {
							attrs: {onclick: action['ONCLICK'], "data-pseudo-name": actionName},
							text: action['TEXT'],
							style: {
								width: '145px'
							},
							props: {
								className: 'js-panel-button bx-disk-btn bx-disk-btn-medium bx-disk-btn-lightgray'
							}
						}));
						break;
					case 'internal_link':
						internalLink = action['PSEUDO_VALUE'];
						break;
				}
			}
		}

		return buttons;
	};

	FolderListClass.prototype.getActions = function(objectId)
	{
		var row = this.getRow(objectId);
		if(!row)
		{
			return null;
		}
		return row.oncontextmenu();
	};

	FolderListClass.prototype.getActionByPseudoName = function(objectId, pseudoName)
	{
		var actions = this.getActions(objectId);
		if(!actions)
		{
			return null;
		}

		for (var i in actions)
		{
			if (!actions.hasOwnProperty(i))
			{
				continue;
			}

			if(actions[i].PSEUDO_NAME === pseudoName)
			{
				return actions[i];
			}
		}

		return null;
	};

	FolderListClass.prototype.getRowByCheckBox = function(checkbox)
	{
		return this.grid.getRowByCheckBox(checkbox);
	};

	FolderListClass.prototype.getCheckbox = function(objectId)
	{
		return this.grid.getCheckbox(objectId);
	};

	FolderListClass.prototype.renameInline = function(objectId)
	{
		var checkbox = this.getCheckbox(objectId);
		var bxGrid = this.grid;
		var gridID = bxGrid.table_id;

		if (checkbox.checked !== true) {
			checkbox.checked = true;
			bxGrid.SelectRow(checkbox);
			bxGrid.EnableActions();
		}
		var tmp_oSaveData = {};
		for (var row_id in bxGrid.oSaveData) {
			if(!bxGrid.oSaveData.hasOwnProperty(row_id))
			{
				continue;
			}
			tmp_oSaveData[row_id] = {};
			for (col_id in bxGrid.oSaveData[row_id]) {
				if(!bxGrid.oSaveData[row_id].hasOwnProperty(col_id))
				{
					continue;
				}
				tmp_oSaveData[row_id][col_id] = bxGrid.oSaveData[row_id][col_id];
			}
		}
		bxGrid.ActionEdit();
		for (row_id in tmp_oSaveData)
		{
			if(!tmp_oSaveData.hasOwnProperty(row_id))
			{
				continue;
			}

			for (var col_id in tmp_oSaveData[row_id])
			{
				if(!tmp_oSaveData[row_id].hasOwnProperty(col_id))
				{
					continue;
				}
				bxGrid.oSaveData[row_id][col_id] = tmp_oSaveData[row_id][col_id];
			}
		}
		var input = BX.findChild(this.getRowByCheckBox(checkbox), {
			tag: 'input',
			attr: {type: 'text'}
		}, true);
		if(input)
		{
			BX.focus(input);
		}

		var btnCancel = BX.findChild(BX('bx_grid_' + gridID + '_action_buttons'), {
			tag: 'input',
			attr: {'type': 'button'}
		});
		btnCancel.onclick = function () {
			bxGrid.ActionCancel();
			var chCells = BX.findChild(BX(gridID), {'tag': 'td', 'class': 'bx-checkbox-col'}, true, true);
			for (var i = 0; i < chCells.length; i++) {
				var cBox = BX.findChild(chCells[i], {'tag': 'input'});
				if (BX.type.isDomNode(cBox) && cBox.checked) {
					cBox.checked = false;
					bxGrid.SelectRow(cBox);
				}
			}
			bxGrid.EnableActions();
		};
	};

	FolderListClass.prototype.openConfirmDelete = function (parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;
		var isFolder = parameters.object.isFolder;
		var canDelete = parameters.canDelete;
		var messageDescription = '';
		if (isFolder) {
			messageDescription = BX.message(canDelete? 'DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FOLDER_CONFIRM' : 'DISK_FOLDER_LIST_TRASH_DELETE_FOLDER_CONFIRM');
		} else {
			messageDescription = BX.message(canDelete? 'DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FILE_CONFIRM' : 'DISK_FOLDER_LIST_TRASH_DELETE_FILE_CONFIRM');
		}
		var buttons = [
			new BX.PopupWindowButton({
				text: BX.message("DISK_FOLDER_LIST_TRASH_DELETE_BUTTON"),
				className: "popup-window-button-accept",
				events: {
					click: BX.delegate(function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);

						BX.Disk.ajax({
							method: 'POST',
							dataType: 'json',
							url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'markDelete'),
							data: {
								objectId: objectId
							},
							onsuccess: BX.delegate(function (data) {
								if (!data) {
									return;
								}
								if(data.status == 'success')
								{
									this.removeRow(objectId, function(){
										BX.Disk.showModalWithStatusAction(data);
									});
									return;
								}
								BX.Disk.showModalWithStatusAction(data);
							}, this)
						});

						return false;
					}, this)
				}
			})
		];
		if (canDelete) {
			buttons.push(
				new BX.PopupWindowButton({
					text: BX.message("DISK_FOLDER_LIST_TRASH_DESTROY_BUTTON"),
					events: {
						click: BX.delegate(function (e)
						{
							BX.PopupWindowManager.getCurrentPopup().destroy();
							BX.PreventDefault(e);

							BX.Disk.ajax({
								method: 'POST',
								dataType: 'json',
								url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'delete'),
								data: {
									objectId: objectId
								},
								onsuccess: BX.delegate(function (data)
								{
									if (!data) {
										return;
									}
									if (data.status == 'success') {
										this.removeRow(objectId, function ()
										{
											BX.Disk.showModalWithStatusAction(data);
										});
										return;
									}
									BX.Disk.showModalWithStatusAction(data);
								}, this)
							});

							return false;
						}, this)
					}
				}));
		}
		buttons.push(
			new BX.PopupWindowButton({
				text: BX.message("DISK_FOLDER_LIST_TRASH_CANCEL_DELETE_BUTTON"),
				events: {
					click: function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);
						return false;
					}
				}
			})
		);

		BX.Disk.modalWindow({
			modalId: 'bx-link-unlink-confirm',
			title: BX.message('DISK_FOLDER_LIST_TRASH_DELETE_TITLE'),
			contentClassName: 'tac',
			contentStyle: {
				paddingTop: '70px',
				paddingBottom: '70px'
			},
			content: messageDescription.replace('#NAME#', name),
			buttons: buttons
		});
	};

	FolderListClass.prototype.openConfirmDetach = function (parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;
		var isFolder = parameters.object.isFolder;
		var onSuccess = parameters.onSuccess;
		var messageDescription = '';
		if (isFolder) {
			messageDescription = BX.message('DISK_FOLDER_LIST_DETACH_FOLDER_CONFIRM');
		} else {
			messageDescription = BX.message('DISK_FOLDER_LIST_DETACH_FILE_CONFIRM');
		}
		var buttons = [
			new BX.PopupWindowButton({
				text: BX.message('DISK_FOLDER_LIST_DETACH_BUTTON'),
				className: "popup-window-button-accept",
				events: {
					click: BX.delegate(function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);

						BX.Disk.ajax({
							method: 'POST',
							dataType: 'json',
							url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'detach'),
							data: {
								objectId: objectId
							},
							onsuccess: BX.delegate(function (data) {
								if (!data) {
									return;
								}
								if(data.status == 'success')
								{
									if(BX.type.isFunction(onSuccess))
									{
										BX.delegate(onSuccess, this)(data);
									}
									else
									{
										this.removeRow(objectId, function(){
											BX.Disk.showModalWithStatusAction(data);
										});
									}
									return;
								}
								BX.Disk.showModalWithStatusAction(data);
							}, this)
						});

						return false;
					}, this)
				}
			}),
			new BX.PopupWindowButton({
				text: BX.message('DISK_FOLDER_LIST_TRASH_CANCEL_DELETE_BUTTON'),
				events: {
					click: function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);
						return false;
					}
				}
			})
		];

		BX.Disk.modalWindow({
			modalId: 'bx-link-unlink-confirm',
			title: isFolder? BX.message('DISK_FOLDER_LIST_DETACH_FOLDER_TITLE') : BX.message('DISK_FOLDER_LIST_DETACH_FILE_TITLE'),
			contentClassName: 'tac',
			contentStyle: {
				paddingTop: '70px',
				paddingBottom: '70px'
			},
			content: messageDescription.replace('#NAME#', name),
			buttons: buttons
		});
	};

	FolderListClass.prototype.openConfirmDeleteGroup = function (parameters)
	{
		var messageDescription = BX.message('DISK_FOLDER_LIST_TRASH_DELETE_GROUP_CONFIRM');
		var buttons = [
			new BX.PopupWindowButton({
				text: BX.message('DISK_FOLDER_LIST_TRASH_DELETE_BUTTON'),
				className: "popup-window-button-accept",
				events: {
					click: BX.delegate(function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);

						this.grid.ActionDelete();
						return false;
					}, this)
				}
			}),

			new BX.PopupWindowButton({
				text: BX.message('DISK_FOLDER_LIST_TRASH_CANCEL_DELETE_BUTTON'),
				events: {
					click: function (e) {
						BX.PopupWindowManager.getCurrentPopup().destroy();
						BX.PreventDefault(e);
						return false;
					}
				}
			})
		];

		BX.Disk.modalWindow({
			modalId: 'bx-link-unlink-confirm',
			title: BX.message('DISK_FOLDER_LIST_TRASH_DELETE_TITLE'),
			contentClassName: 'tac',
			contentStyle: {
				paddingTop: '70px',
				paddingBottom: '70px'
			},
			content: messageDescription,
			buttons: buttons
		});
	};

	FolderListClass.prototype.connectObjectToDisk = function (parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;
		var isFolder = parameters.object.isFolder;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'connectToUserStorage'),
			{
				id: 'folder_list_connect_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: function(response)
				{
					if(!response)
					{
						return;
					}
					if(response.status == 'success')
					{
						BX.Disk.showModalWithStatusAction({
							status: 'success',
							message: isFolder?
								BX.message('DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FOLDER').replace('#NAME#', name) :
								BX.message('DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FILE').replace('#NAME#', name)
						})
					}
					else
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}
				}
			}
		);
	};

	FolderListClass.prototype.unlockFile = function (parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'unlock'),
			{
				id: 'folder_list_unlock_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: BX.delegate(function(response){
					if(!response)
					{
						return;
					}
					if(response.status == 'success')
					{
						var action = this.getActionByPseudoName(objectId, 'lock');
						if(action)
						{
							action.SHOW = true;
						}
						action = this.getActionByPseudoName(objectId, 'unlock');
						if(action)
						{
							action.SHOW = false;
						}

						var unlockButton = this.getButtonInInfoPanelByPseudoName('unlock');
						if(unlockButton)
						{
							unlockButton.parentNode.replaceChild(this.getActionButtonByPseudoName(objectId, 'lock'), unlockButton);
						}

						this.hideLockIcon(objectId);

						BX.Disk.showModalWithStatusAction({
							status: 'success',
							message: BX.message('DISK_FOLDER_LIST_SUCCESS_UNLOCKED_FILE').replace('#NAME#', name)						})
					}
					else
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}
				}, this)
			}
		);
	};

	FolderListClass.prototype.lockFile = function (parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'lock'),
			{
				id: 'folder_list_lock_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: BX.delegate(function(response){
					if(!response)
					{
						return;
					}
					if(response.status == 'success')
					{
						var action = this.getActionByPseudoName(objectId, 'lock');
						if(action)
						{
							action.SHOW = false;
						}
						action = this.getActionByPseudoName(objectId, 'unlock');
						if(action)
						{
							action.SHOW = true;
						}

						var lockButton = this.getButtonInInfoPanelByPseudoName('lock');
						if(lockButton)
						{
							lockButton.parentNode.replaceChild(this.getActionButtonByPseudoName(objectId, 'unlock'), lockButton);
						}

						this.showLockIcon(objectId);

						BX.Disk.showModalWithStatusAction({
							status: 'success',
							message: BX.message('DISK_FOLDER_LIST_SUCCESS_LOCKED_FILE').replace('#NAME#', name)
						})
					}
					else
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}
				}, this)
			}
		);
	};

	FolderListClass.prototype.showLockIcon = function(objectId)
	{
		var row = this.getRow(objectId);
		var lockIcon = BX.findChildByClassName(row, 'js-lock-icon', true);
		if(lockIcon)
		{
			BX.show(lockIcon, 'block');
		}
	};

	FolderListClass.prototype.hideLockIcon = function(objectId)
	{
		var row = this.getRow(objectId);
		var lockIcon = BX.findChildByClassName(row, 'js-lock-icon', true);
		if(lockIcon)
		{
			BX.hide(lockIcon, 'block');
		}
	};

	FolderListClass.prototype.showShareInfoSmallView = function(parameters)
	{
		var name = parameters.object.name;
		var objectId = parameters.object.id;
		var isFolder = parameters.object.isFolder;
		var bindElement = null;

		var e = window.event;
		if(e) {
			bindElement = e.target || e.srcElement;
		}
		function createNodeForUser(data)
		{
			if(data.type == 'users')
			{
				return BX.create('a', {
					props: {
						href: data.url || BX.Disk.getPathToUser(data.entityId),
						target: '_blank',
						className: 'bx-disk-people'
					},
					children: [
						(data.avatar?
						BX.create('span', {
							style: {
								backgroundImage: 'url(' + data.avatar + ')'
							},
							props: {
								className: 'bx-disk-avatar'
							}
						}) : null),
						BX.create('span', {
							text: data.name
						})
					]
				});
			}
			else
			{
				return BX.create('span', {
					props: {
						className: 'bx-disk-people'
					},
					children: [
						(data.avatar?
						BX.create('span', {
							style: {
								backgroundImage: 'url(' + data.avatar + ')'
							},
							props: {
								className: 'bx-disk-avatar'
							}
						}) : null),
						BX.create('span', {
							text: data.name
						})
					]
				});
			}
		}

		BX.Disk.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showShareInfoSmallView'),
			data: {
				objectId: objectId
			},
			onsuccess: function (response) {
				if (!response || response.status != 'success')
				{
					return;
				}
				var usersNode = [];
				response.members = response.members || [];
				for (var i in response.members)
				{
					if (!response.members.hasOwnProperty(i))
					{
						continue;
					}
					usersNode.push(createNodeForUser(response.members[i]));
					usersNode.push(', ');
				}
				if(usersNode)
				{
					usersNode.pop();
				}

				BX.Disk.modalWindow({
					closeIcon: null,
					autoHide: true,
					overlay: false,
					bindElement: bindElement,
					modalId: 'bx-disk-share-info-small-view',
					withoutContentWrap: true,
					events: {
						onAfterPopupShow: function () {
						},
						onPopupClose: function () {
							this.destroy();
						}
					},
					content: [
						BX.create('div', {
							props: {
								className: 'bx-disk-popup-share-list'
							},
							style: {
								width: '450px'
							},
							children: [
								BX.message('DISK_FOLDER_LIST_DETAIL_SHARE_INFO_OWNER') + ':',
								BX.create('br'),
								createNodeForUser(response.owner),
								BX.create('br'),
								BX.message('DISK_FOLDER_LIST_DETAIL_SHARE_INFO_HAVE_ACCESS') + ':',
								BX.create('br')
							].concat(usersNode)
						})
					]
				});
			}
		});
	};

	FolderListClass.prototype.getFirstSelectedCheckbox = function()
	{
		var i;
		for (i in this.checkboxes) {
			if (this.checkboxes.hasOwnProperty(i) && typeof(i) !== 'function' && this.checkboxes[i] == true) {
				break;
			}
		}
		return this.getCheckbox(i);
	};

	FolderListClass.prototype.addCheckbox = function(checkbox)
	{
		var objectId = checkbox.value;
		if(!this.checkboxes[objectId])
		{
			this.checkboxes[objectId] = true;
		}
	};

	FolderListClass.prototype.deleteCheckbox = function(checkbox)
	{
		var objectId = checkbox.value;
		if(this.checkboxes[objectId])
		{
			this.checkboxes[objectId] = false;
		}
	};

	FolderListClass.prototype.onClickGetDownloadArchiveInfoPanelButton = function (e, objectIds)
	{
		objectIds = objectIds || [];
		BX.Disk.ajax({
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'getUrlToDownloadArchive'),
			method: 'POST',
			dataType: 'json',
			data: {
				folderId: this.currentFolder.id,
				objectIds: objectIds
			},
			onsuccess: BX.delegate(function(response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				document.location = response.downloadArchiveUrl;

			}, this)
		});

	};

	FolderListClass.prototype.onClickCopyInInfoPanelButton = function (e)
	{
		var targetObjectId = null;
		var targetObjectNode = null;
		BX.addCustomEvent("onSelectFolder", BX.delegate(function(node){
			if(targetObjectNode)
			{
				BX.removeClass(targetObjectNode, 'selected');
			}
			targetObjectId = node.getAttribute('data-object-id');
			targetObjectNode = node;
			var td = BX.findChild(targetObjectNode, {className: 'bx-disk-wf-folder-name'}, true);
			if(td)
			{
				var label = BX.findChild(this.gridShowTreeButton, {tagName: 'span'});
				if(label)
				{
					var nodeWithName = BX.findChild(td, {tagName: 'span'}) || {};
					BX.adjust(label, {text: nodeWithName.textContent || nodeWithName.innerText});
				}
			}
			targetObjectId && this.setGroupActionTargetObjectId(targetObjectId);

			if(BX.PopupWindowManager.isPopupExists('bx-disk-toolbar-tree'))
			{
				if(BX.PopupWindowManager.getCurrentPopup())
				{
					BX.PopupWindowManager.getCurrentPopup().close();
				}
			}
		}, this));
		BX.addCustomEvent("onUnSelectFolder", function(node){
			targetObjectId = null;
			targetObjectNode = null;
			var pos = BX('grid_group_action_target_object');
			pos && BX.remove(pos);
		});


		BX.Disk.modalWindowLoader(BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSubFoldersToAdd'), {
			id: 'bx-disk-copy-modal-loader',
			responseType: 'json',
			postData: {
				objectId: this.rootObject.id
			},
			afterSuccessLoad: BX.delegate(function(response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				var rootNode = this.buildTreeNode(this.rootObject);
				var ul = BX.create('ul', {
					props: {
						className: 'bx-disk-wood-folder'
					}
				});
				rootNode.appendChild(ul);
				BX.Disk.modalWindow({
					modalId: 'bx-disk-copy-tree',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_TREE'),
					events: {
						onPopupClose: function () {
							this.destroy();
						}
					},
					content: [
						BX.create('div', {
							props: {
								className: 'bx-disk-popup-content-title'
							},
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_MANY_COPY_TO')
						}),
						BX.create('ul', {
							props: {
								className: 'bx-disk-wood-folder'
							},
							children: [rootNode]
						})
					],
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO_BUTTON'),
							className: 'webform-button-active',
							events: {
								click: BX.delegate(function (e) {
									BX.PopupWindowManager.getCurrentPopup().close();
									BX.PreventDefault(e);

									this.actionGroupButton = 'copy';
									this.grid.SetActionName('copy');

									BX.submit(this.grid.GetForm());

									return false;
								}, this)
							}
						})
					]
				});

				this.buildTree(rootNode, response);
			}, this)
		});
	};

	FolderListClass.prototype.areObjectsFiles = function(objectIds)
	{
		for (var i in objectIds) {
			if (!objectIds.hasOwnProperty(i)) {
				continue;
			}
			var objectId = objectIds[i];

			var objectData = BX.delegate(getObjectDataId, this)(objectId);
			if(BX.hasClass(objectData.icon, 'bx-disk-folder-icon') )
			{
				return false;
			}
		}

		return true;
	};

	FolderListClass.prototype.showInfoPanelManyObject = function(objectIds)
	{
		var iconClass = 'bx-disk-folder-icon double';
		var buttons = [];

		if(this.enabledModZip && this.areObjectsFiles(objectIds))
		{
			buttons.push(BX.create('a', {
				text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DOWNLOAD_BUTTON'),
				style: {
					width: '145px'
				},
				props: {
					className: 'bx-disk-btn bx-disk-btn-medium bx-disk-btn-green'
				},
				events: {
					click: BX.delegate(function(e){
						return this.onClickGetDownloadArchiveInfoPanelButton(e, objectIds);
					}, this)
				}
			}));
			buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
		}
		buttons.push(BX.create('a', {
			text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_COPY_TO_BUTTON'),
			style: {
				width: '145px'
			},
			props: {
				className: 'bx-disk-btn bx-disk-btn-medium bx-disk-btn-lightgray'
			},
			events: {
				click: BX.proxy(this.onClickCopyInInfoPanelButton, this)
			}
		}));
		buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
		buttons.push(BX.create('a', {
			text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DELETE_BUTTON'),
			style: {
				width: '145px'
			},
			props: {
				className: 'bx-disk-btn bx-disk-btn-medium bx-disk-btn-lightgray'
			},
			events: {
				click: BX.delegate(function(e){
					BX.fireEvent(BX('delete_button_' + this.grid.table_id), 'click');
				}, this)
			}
		}));

		this.showInfoPanel({
			buttons: buttons,
			isFolder: false,
			isMany: true,
			icon: {
				className: iconClass
			},
			title: {
				text: BX.Disk.getNumericCase(objectIds.length, BX.message('DISK_FOLDER_LIST_SELECTED_OBJECT_1'), BX.message('DISK_FOLDER_LIST_SELECTED_OBJECT_21'), BX.message('DISK_FOLDER_LIST_SELECTED_OBJECT_2_4'), BX.message('DISK_FOLDER_LIST_SELECTED_OBJECT_5_20')).replace('#COUNT#', objectIds.length)
			}
		});
	};

	FolderListClass.prototype.showInfoPanelSingleObject = function(objectId)
	{
		this.currentObjectIdInInfoPanel = objectId;
		var internalLink = '';

		var objectData = BX.delegate(getObjectDataId, this)(objectId);

		var row = objectData.row;
		var title = objectData.title;
		var icon = objectData.icon;

		var iconClass = 'bx-file-icon-container-small bx-disk-file-icon';
		if(icon)
		{
			iconClass = icon.className;
		}

		if(!title)
		{
			return;
		}
		if(!row.oncontextmenu)
		{
			return;
		}

		this.showInfoPanel({
			buttons: this.getActionButtons(objectId),
			internalLink: internalLink,
			isFolder: BX.hasClass(icon, 'bx-disk-folder-icon'),
			icon: {
				className: iconClass
			},
			objectId: objectId,
			title: {
				text: title.text,
				date: title.getAttribute('data-bx-dateModify'),
				href: title.getAttribute('href')
			}
		});
	};

	function getActionDataFromRowByPseudoName(row, pseudoName)
	{
		if(!row.oncontextmenu)
		{
			return {};
		}
		var actions = row.oncontextmenu();
		for (var i in actions) {
			if (!actions.hasOwnProperty(i))
			{
				continue;
			}
			var action = actions[i];
			if(!action || !action['PSEUDO_NAME'])
			{
				continue;
			}
			if(action['PSEUDO_NAME'] === pseudoName)
			{
				return action;
			}
		}

		return {};
	}

	FolderListClass.prototype.getButtonInInfoPanelByPseudoName = function(pseudoName)
	{
		var buttons = BX.findChildByClassName(this.infoPanelContainer, 'js-info-panel-buttons');
		if(!buttons)
		{
			return null;
		}

		return BX.findChild(buttons, {
			className: 'js-panel-button',
			attribute: {
				'data-pseudo-name': pseudoName
			}
		}, true);
	};

	FolderListClass.prototype.createButtonsInInfoPanel = function(params)
	{
		var icon = params.icon;
		var title = params.title;
		var buttons = params.buttons;
		return BX.create('div', {
				props: {
					className: 'js-info-panel bx-disk-info-panel bx-disk-sidebar-section'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'bx-disk-info-panel-relative'
						},
						children: [
							BX.create('div', {
								props: {
									className: 'bx-disk-info-panel-icon'
								},
								children: [
									BX.create('div', {
										props: {
											className: icon.className
										}
									})
								]
							}),
							BX.create('div', {
								props: {
									className: 'bx-disk-info-panel-element-name-container'
								},
								children: [
									BX.create('div', {
										props: {
											id: 'disk_info_panel_name',
											className: 'bx-disk-info-panel-name'
										},
										children: [
											BX.create('a', {
												text: title.text,
												props: {
													title: title.text,
													href: title.href || 'javascript:void(0);'
												}
											})
										]
									}),
									BX.create('div', {
										text: title.date,
										props: {
											className: 'bx-disk-info-panel-date'
										}
									})
								]
							}),
							BX.create('div', {
								props: {
									className: 'bx-disk-info-panel-context'
								}
							})
						]
					}),
					BX.create('div', {
						props: {
							className: 'tal js-info-panel-buttons'
						},
						children: buttons
					})
				]
		});
	};

	FolderListClass.prototype.showInfoPanel = function(params)
	{
		var title = params.title || {};
		var icon = params.icon || {};
		var buttons = params.buttons || [];
		var internalLink = params.internalLink || '';
		var isFolder = params.isFolder;
		var isMany = !!params.isMany;
		var objectId = params.objectId || null;

		if(!title)
		{
			return;
		}

		var infoPanelContainer = BX('disk_info_panel');
		var emptyContainer = BX('bx_disk_empty_select_section');
		if(emptyContainer)
			BX.hide(emptyContainer);

		infoPanelContainer.style.overflow = 'hidden';
		infoPanelContainer.style.height = 0;
		BX.cleanNode(infoPanelContainer);
		this.containerWithExtAndIntLinks = null;
		var child = BX.create('div');
		infoPanelContainer.appendChild(child);


		var buttonsNode = this.createButtonsInInfoPanel({
			title: title,
			icon: icon,
			buttons: buttons
		});
		child.appendChild(buttonsNode);

		if(objectId && !isMany && this.enabledExternalLink)
		{
			var containerWithExtAndIntLinks = this.getContainerWithExtAndIntLinks({
				objectId: objectId,
				internalLink: internalLink
			});
			child.appendChild(
				containerWithExtAndIntLinks
			);
		}
		else
		{
			this.hideContainerWithExtAndIntLinks();
		}

		(new BX.easing({
			duration : 300,
			start : { opacity: 0, height : 0},
			finish : { opacity : 100, height : child.offsetHeight},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
			step : function(state) {
				infoPanelContainer.style.height = state.height + "px";
				infoPanelContainer.style.opacity = state.opacity / 100;
			},
			complete : BX.delegate(function() {
				infoPanelContainer.style.cssText = "";
				infoPanelContainer.style.display = "block";
			}, this)
		})).animate();
	};

	FolderListClass.prototype.showContainerWithExtAndIntLinks = function()
	{
		if(!this.containerWithExtAndIntLinks)
		{
			return;
		}
		BX.show(this.containerWithExtAndIntLinks, 'block');
	};

	FolderListClass.prototype.hideContainerWithExtAndIntLinks = function()
	{
		if(!this.containerWithExtAndIntLinks)
		{
			return;
		}
		BX.hide(this.containerWithExtAndIntLinks);
		this.switchOffExtLinkInPanel();
	};

	FolderListClass.prototype.getExternalLinkByObjectId = function(objectId)
	{
		if(this.cacheExternalLinks[objectId])
		{
			return this.cacheExternalLinks[objectId];
		}
		BX.Disk.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'getExternalLink'),
			data: {
				objectId: objectId
			},
			onsuccess: BX.delegate(function (response) {
				if(!response || response.status != 'success')
				{
					return;
				}

				if(response.link)
				{
					this.switchOnExtLinkInPanel(response);
					this.cacheExternalLinks[objectId] = response.link;
				}
				else
				{
					this.switchOffExtLinkInPanel();
				}

			}, this)
		});
		return '';
	};

	FolderListClass.prototype.switchOffExtLinkInPanel = function(params)
	{
		var extInput = BX('bx-disk-sidebar-shared-outlink-input');
		var label = BX('bx-disk-sidebar-shared-outlink-label');
		if(!extInput || !label)
			return;
		extInput.value = '';
		BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_OFF')});
		BX.removeClass(label.parentNode, 'on');
		BX.addClass(label.parentNode, 'off');
		BX.hide(BX('bx-disk-sidebar-shared-param'));
	};

	FolderListClass.prototype.switchOnExtLinkInPanel = function(params)
	{
		var extInput = BX('bx-disk-sidebar-shared-outlink-input');
		var label = BX('bx-disk-sidebar-shared-outlink-label');
		if(!extInput || !label)
			return;
		extInput.value = params.link || '';
		BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON')});
		BX.removeClass(label.parentNode, 'off');
		BX.addClass(label.parentNode, 'on');
		BX.show(BX('bx-disk-sidebar-shared-param'), 'block');
	};

	FolderListClass.prototype.switchOffExtLinkInModal = function(params)
	{
		var extInput = BX('bx-disk-ext-link-url');
		var label = BX('bx-disk-sidebar-shared-outlink-label-modal');
		if(!extInput || !label)
			return;
		extInput.value = '';
		BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_OFF')});
		BX.removeClass(label.parentNode, 'on');
		BX.addClass(label.parentNode, 'off');
		BX('bx-disk-extended-settings-extlink') && BX.hide(BX('bx-disk-extended-settings-extlink'));
		BX('bx-disk-extended-text-extlink') && BX.hide(BX('bx-disk-extended-text-extlink'));
	};

	FolderListClass.prototype.switchOnExtLinkInModal = function(params)
	{
		var extInput = BX('bx-disk-ext-link-url');
		var label = BX('bx-disk-sidebar-shared-outlink-label-modal');
		if(!extInput || !label)
			return;
		extInput.value = params.link || '';
		BX.adjust(label, {text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON')});
		BX.removeClass(label.parentNode, 'off');
		BX.addClass(label.parentNode, 'on');
		BX.show(BX('bx-disk-extended-settings-extlink'), 'block');
	};

	FolderListClass.prototype.onClickExternalSwitcherSidebar = function(e)
	{
		if(!this.currentObjectIdInInfoPanel)
		{
			return;
		}
		var target = e.currentTarget;
		var objectData = BX.delegate(getObjectDataId, this)(this.currentObjectIdInInfoPanel);
		var isFolder = BX.hasClass(objectData.icon, 'bx-disk-folder-icon');
		var queryUrl = this.ajaxUrl;

		if(BX.hasClass(target.firstChild, 'on'))
		{
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'action', 'disableExternalLink');
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

			BX.Disk.ajax({
				method: 'POST',
				dataType: 'json',
				url: queryUrl,
				data: {
					objectId: this.currentObjectIdInInfoPanel
				},
				onsuccess: BX.delegate(function (response) {
					this.cacheExternalLinks[this.currentObjectIdInInfoPanel] = '';
				}, this)
			});
			this.switchOffExtLinkInPanel();
		}
		else
		{
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'action', 'generateExternalLink');
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

			BX.Disk.ajax({
				method: 'POST',
				dataType: 'json',
				url: queryUrl,
				data: {
					objectId: this.currentObjectIdInInfoPanel
				},
				onsuccess: BX.delegate(function (response) {
					if (!response || response.status != 'success') {
						return;
					}

					this.switchOnExtLinkInPanel(response);
					this.cacheExternalLinks[this.currentObjectIdInInfoPanel] = response.link;
				}, this)
			});
		}
	};

	FolderListClass.prototype.onClickExternalSwitcherModal = function(e)
	{
		var target = e.currentTarget;
		var objectId = target.getAttribute('bx-disk-objectId');

		var objectData = BX.delegate(getObjectDataId, this)(objectId);
		var isFolder = BX.hasClass(objectData.icon, 'bx-disk-folder-icon');
		var queryUrl = this.ajaxUrl;

		if(BX.hasClass(target.firstChild, 'on'))
		{
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'action', 'disableExternalLink');
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

			BX.Disk.ajax({
				method: 'POST',
				dataType: 'json',
				url: queryUrl,
				data: {
					objectId: objectId
				},
				onsuccess: BX.delegate(function (response) {
					this.cacheExternalLinks[objectId] = '';
				}, this)
			});
			this.switchOffExtLinkInPanel();
			this.switchOffExtLinkInModal();
		}
		else
		{
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'action', 'generateExternalLink');
			queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

			BX.Disk.ajax({
				method: 'POST',
				dataType: 'json',
				url: queryUrl,
				data: {
					objectId: objectId
				},
				onsuccess: BX.delegate(function (response) {
					if (!response || response.status != 'success') {
						return;
					}

					this.switchOnExtLinkInPanel(response);
					this.switchOnExtLinkInModal(response);
					this.cacheExternalLinks[objectId] = response.link;
				}, this)
			});
		}
	};

	FolderListClass.prototype.openExternalLinkDetailSettings = function (e)
	{
		BX.PreventDefault(e);
		BX.Disk.modalWindowLoader(BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'getDetailSettingsExternalLink'), {
			id: 'bx-disk-external-link-loader',
			responseType: 'json',
			postData: {
				objectId: this.currentObjectIdInInfoPanel
			},
			afterSuccessLoad: BX.delegate(function(response){

				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}

				var contentHtml;
				if(response.linkData.hasDeathTime || response.linkData.hasPassword)
				{
					contentHtml =
						'<div class="bx-disk-popup-content-title noborder">' +
							response.object.name + ' ' +
							'<span class="fwn">' + response.object.size + ', ' + response.object.date + '</span>' +
						'</div>' +
						(response.linkData.hasDeathTime? '<div>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_DEATH_TIME').replace('#DATE#', response.linkData.deathTime) + ' </div>' : '') +
						(response.linkData.hasPassword? '<div>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_PASSWORD') + ' </div>' : '')
					;
				}
				else
				{
					contentHtml =
						'<div class="bx-disk-popup-content-title noborder">' +
							response.object.name + ' ' +
							'<span class="fwn">' + response.object.size + ', ' + response.object.date + '</span>' +
						'</div>' +

						'<form autocomplete="false" action="">' +
							'<ul class="bx-disk-popup-share-link-param-list">' +
								'<li>' +
									'<input id="bx-disk-ext-use-death-time" ' + (response.linkData.hasDeathTime? 'checked="checked"' : '') + ' type="checkbox">' +
									'<label for="bx-disk-ext-use-death-time">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_DEATH_TIME') + ' </label>' +
									'<span id="bx-disk-ext-use-death-time-table" ' + (response.linkData.hasDeathTime? '' : 'style="display: none;"') + '><input id="bx-disk-ext-use-death-time-value" type="text" autocomplete="false" class="bx-disk-popup-input bx-disk-popup-share-input" style="height: 31px;">' +
									'<span id="bx-disk-ext-select-time" class="popup-control" style="vertical-align: initial; height: 32px;">' +
										'<span class="popup-current">' +
											'<span class="popup-current-text fwn" style="line-height: 29px;height: 29px;">' +
												'<span id="bx-disk-ext-select-time-text">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR') + '</span>' +
												'<span class="icon-arrow"></span>' +
											'</span>' +
										'</span>' +
									'</span></span>' +
								'</li>' +
								'<li>' +
									'<input id="bx-disk-ext-use-password" ' + (response.linkData.hasPassword? 'checked="checked"' : '') + ' type="checkbox">' +
									'<label for="bx-disk-ext-use-password">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_PASSWORD') + '</label>' +
									'<table id="bx-disk-ext-use-password-table" class="bx-disk-popup-share-password" ' + (response.linkData.hasPassword? '' : 'style="display: none;"') + '>' +
										'<tbody><tr>' +
											'<td>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_INPUT_PASSWORD') + ':</td>' +
											'<td><input autocomplete="new-password" id="bx-disk-ext-use-password-value" type="password" class="bx-disk-popup-input" style="height: 31px;"></td>' +
										'</tr>' +
									'</tbody></table>' +
								'</li>' +
							'</ul>' +
						'</form>'
					;

				}

				var multSeconds = 60*60;
				var buttons = [
					new BX.PopupWindowButton({
						id: 'bx-disk-btn-save',
						text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
						className: "popup-window-button-accept",
						events: {
							click: BX.delegate(function (){
								var deathTimeMinutes = (parseInt(BX('bx-disk-ext-use-death-time-value').value, 10) || 0) * multSeconds;

								BX.Disk.ajax({
									method: 'POST',
									dataType: 'json',
									url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'saveSettingsExternalLink'),
									data: {
										objectId: this.currentObjectIdInInfoPanel,
										deathTime: deathTimeMinutes,
										password: BX('bx-disk-ext-use-password-value').value
									},
									onsuccess: function (response)
									{
										BX.PopupWindowManager.getCurrentPopup().close();

										BX.Disk.showModalWithStatusAction(response);

									}
								});

							}, this)
						}
					}),
					new BX.PopupWindowButton({
						text: BX.message('DISK_FOLDER_TOOLBAR_BTN_CLOSE'),
						events: {
							click: function () {
								BX.PopupWindowManager.getCurrentPopup().close();
							}
						}
					})
				];

				var callbackChanger = BX.delegate(function (){
					var alreadyRun = false;
					return function ()
					{
						if(alreadyRun)
							return;
						alreadyRun = true;

						BX.show(BX('bx-disk-btn-save'));
					};
				}, this)();

				BX.Disk.modalWindow({
					modalId: 'bx-disk-external-link',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_GET_EXT_LINK'),
					events: {
						onPopupShow: function(){
							BX.hide(BX('bx-disk-btn-save'));
						},
						onAfterPopupShow: BX.delegate(function () {
							BX.bind(BX('bx-disk-ext-use-password'), 'click', function(e){
								if(this.checked)
								{
									BX.show(BX('bx-disk-ext-use-password-table'), 'block');
								}
								else
								{
									BX('bx-disk-ext-use-password-value').value = '';
									BX.hide(BX('bx-disk-ext-use-password-table'));
								}
							});

							BX.bind(BX('bx-disk-ext-use-password-value'), 'change', BX.once(BX('bx-disk-ext-use-password-value'), 'change', BX.delegate(callbackChanger, this)));
							BX.bind(BX('bx-disk-ext-use-death-time-value'), 'change', BX.once(BX('bx-disk-ext-use-death-time-value'), 'change', BX.delegate(callbackChanger, this)));

							BX.bind(BX('bx-disk-ext-use-death-time'), 'click', function(e){
								if(this.checked)
								{
									BX.show(BX('bx-disk-ext-use-death-time-table'), 'inline');
								}
								else
								{
									BX.hide(BX('bx-disk-ext-use-death-time-table'));
								}
							});

							var self = this;
							BX.bind(
								BX('bx-disk-ext-select-time'),
								'click',
								function(){
									BX.PopupMenu.show(
										'bx-disk-ext-select-time',
										this,
										[
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 60;

													this.close();
												}
											},
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 60*60;

													this.close();
												}

											},
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 24*60*60;

													this.close();
												}

											}
										],
										{
											autoHide : true,
											offsetTop: 0,
											zIndex: 10000,
											offsetLeft: 55,
											angle: { offset: 45 },
											events:
											{
												onPopupClose : function(){
												}
											}
										}
									);
								}
							);

						}, this),
						onPopupClose: function () {
							BX.PopupMenu.destroy('bx-disk-ext-select-time');
							this.destroy();
						}
					},
					content: [
						contentHtml
					],
					buttons: buttons
				});
			}, this)
		});

		return false;
	};

	FolderListClass.prototype.openExternalLinkDetailSettingsWithEditing = function (objectId)
	{
		var objectData = BX.delegate(getObjectDataId, this)(objectId);
		var isFolder = BX.hasClass(objectData.icon, 'bx-disk-folder-icon');
		var queryUrl = BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'getDetailSettingsExternalLinkForceCreate');
		queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

		BX.Disk.modalWindowLoader(queryUrl, {
			id: 'bx-disk-external-link-loader',
			responseType: 'json',
			postData: {
				objectId: objectId
			},
			afterSuccessLoad: BX.delegate(function(response){

				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}

				var contentHtml = '';

				contentHtml +=
					'<input id="bx-disk-ext-link-url" style="max-width: 400px;" class="bx-disk-popup-input" type="text" value="' + response.linkData.link + '">'
				;
				if(response.linkData.hasDeathTime || response.linkData.hasPassword)
				{
					contentHtml +=
						'<div id="bx-disk-extended-text-extlink" style="padding-top: 10px;">' +
							(response.linkData.hasDeathTime? '<div>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_DEATH_TIME').replace('#DATE#', response.linkData.deathTime) + ' </div>' : '') +
							(response.linkData.hasPassword? '<div>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_PASSWORD') + ' </div>' : '') +
						'</div>'
					;
				}
				contentHtml +=
					'<div id="bx-disk-extended-settings-extlink"' + (response.linkData.hasDeathTime || response.linkData.hasPassword? 'style="display:none"' : '') + '>' +
						'<div style="padding-top: 10px;">' +
							'<a class="bx-disk-popup-share-link-more" href="#" onclick="BX.toggle(BX(\'bx-disk-popup-share-link-param-modal\')); return false;">' + BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_PARAMS') + '</a>' +
						'</div>' +

						'<div id="bx-disk-popup-share-link-param-modal" style="display: none;"><form autocomplete="false" action="">' +
							'<ul class="bx-disk-popup-share-link-param-list">' +
								'<li>' +
									'<input id="bx-disk-ext-use-death-time" ' + (response.linkData.hasDeathTime? 'checked="checked"' : '') + ' type="checkbox">' +
									'<label for="bx-disk-ext-use-death-time">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_DEATH_TIME') + ' </label>' +
									'<span id="bx-disk-ext-use-death-time-table" ' + (response.linkData.hasDeathTime? '' : 'style="display: none;"') + '><input id="bx-disk-ext-use-death-time-value" type="text" autocomplete="false" class="bx-disk-popup-input bx-disk-popup-share-input" style="height: 31px;">' +
									'<span id="bx-disk-ext-select-time" class="popup-control" style="vertical-align: initial; height: 32px;">' +
										'<span class="popup-current">' +
											'<span class="popup-current-text fwn">' +
												'<span id="bx-disk-ext-select-time-text">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR') + '</span>' +
												'<span class="icon-arrow"></span>' +
											'</span>' +
										'</span>' +
									'</span></span>' +
								'</li>' +
								'<li>' +
									'<input id="bx-disk-ext-use-password" ' + (response.linkData.hasPassword? 'checked="checked"' : '') + ' type="checkbox">' +
									'<label for="bx-disk-ext-use-password">' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_PASSWORD') + '</label>' +
									'<table id="bx-disk-ext-use-password-table" class="bx-disk-popup-share-password" ' + (response.linkData.hasPassword? '' : 'style="display: none;"') + '>' +
										'<tbody><tr>' +
											'<td>' + BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_INPUT_PASSWORD') + ':</td>' +
											'<td><input autocomplete="new-password" id="bx-disk-ext-use-password-value" type="password"  class="bx-disk-popup-input" style="height: 31px;"></td>' +
										'</tr>' +
									'</tbody></table>' +
								'</li>' +
							'</ul>' +
						'</form></div>' +
					'</div>'
				;

				var multSeconds = 60*60;
				var buttons = [
					new BX.PopupWindowButton({
						id: 'bx-disk-btn-save',
						text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
						className: "popup-window-button-accept",
						events: {
							click: BX.delegate(function (){
								var deathTimeMinutes = (parseInt(BX('bx-disk-ext-use-death-time-value').value, 10) || 0) * multSeconds;

								BX.Disk.ajax({
									method: 'POST',
									dataType: 'json',
									url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'saveSettingsExternalLink'),
									data: {
										objectId: objectId,
										deathTime: deathTimeMinutes,
										password: BX('bx-disk-ext-use-password').checked? BX('bx-disk-ext-use-password-value').value : ''
									},
									onsuccess: function (response)
									{
										BX.PopupWindowManager.getCurrentPopup().close();

										BX.Disk.showModalWithStatusAction(response);

									}
								});

							}, this)
						}
					}),
					new BX.PopupWindowButton({
						text: BX.message('DISK_FOLDER_TOOLBAR_BTN_CLOSE'),
						events: {
							click: function () {
								BX.PopupWindowManager.getCurrentPopup().close();
							}
						}
					})
				];

				var callbackChanger = BX.delegate(function (){
					var alreadyRun = false;
					return function ()
					{
						if(alreadyRun)
							return;
						alreadyRun = true;

						BX.show(BX('bx-disk-btn-save'));
					};
				}, this)();

				BX.Disk.modalWindow({
					modalId: 'bx-disk-external-link',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_GET_EXT_LINK'),
					events: {
						onPopupShow: function(){
							BX.hide(BX('bx-disk-btn-save'));
						},
						onAfterPopupShow: BX.delegate(function () {

							BX.focus(BX('bx-disk-ext-link-url'));
							BX('bx-disk-ext-link-url').setSelectionRange(0, BX('bx-disk-ext-link-url').value.length);

							BX.bind(BX('bx-disk-ext-use-password'), 'click', function(e){
								if(this.checked)
								{
									BX.show(BX('bx-disk-ext-use-password-table'), 'block');
								}
								else
								{
									BX('bx-disk-ext-use-password-value').value = '';
									BX.hide(BX('bx-disk-ext-use-password-table'));
								}
							});

							BX.bind(BX('bx-disk-ext-use-password-value'), 'change', BX.once(BX('bx-disk-ext-use-password-value'), 'change', BX.delegate(callbackChanger, this)));
							BX.bind(BX('bx-disk-ext-use-death-time-value'), 'change', BX.once(BX('bx-disk-ext-use-death-time-value'), 'change', BX.delegate(callbackChanger, this)));

							BX.bind(BX('bx-disk-ext-use-death-time'), 'click', function(e){
								if(this.checked)
								{
									BX.show(BX('bx-disk-ext-use-death-time-table'), 'inline');
								}
								else
								{
									BX.hide(BX('bx-disk-ext-use-death-time-table'));
								}
							});

							var self = this;
							BX.bind(
								BX('bx-disk-ext-select-time'),
								'click',
								function(){
									BX.PopupMenu.show(
										'bx-disk-ext-select-time',
										this,
										[
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 60;

													this.close();
												}
											},
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 60*60;

													this.close();
												}

											},
											{
												text: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY'),
												title: BX.message('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY'),
												href: '#',
												onclick: function(e, item){
													BX.PreventDefault(e);
													BX.delegate(callbackChanger, self)();
													BX.adjust(BX('bx-disk-ext-select-time-text'), {text: item.text});
													multSeconds = 24*60*60;

													this.close();
												}

											}
										],
										{
											autoHide : true,
											offsetTop: 0,
											zIndex: 10000,
											offsetLeft: 55,
											angle: { offset: 45 },
											events:
											{
												onPopupClose : function(){
												}
											}
										}
									);
								}
							);

						}, this),
						onPopupClose: function () {
							BX.PopupMenu.destroy('bx-disk-ext-select-time');
							this.destroy();
						}
					},
					content: [
						BX.create('div', {
							props: {
								className: 'bx-disk-sidebar-shared-outlink-switcher-container'
							},
							attrs: {
								'bx-disk-objectId': objectId
							},
							events: {
								click: BX.proxy(this.onClickExternalSwitcherModal, this)
							},
							children: [
								BX.create('div', {
									props: {
										className: 'bx-disk-sidebar-shared-outlink-switcher-track on'
									},
									children: [
										BX.create('span', {
											props: {
												id: 'bx-disk-sidebar-shared-outlink-label-modal'
											},
											text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON')
										}),
										BX.create('div', {
											props: {
												className: 'bx-disk-sidebar-shared-outlink-switcher-point'
											}
										})
									]
								})
							]
						}),
						BX.create('div', {html: contentHtml})
					],
					buttons: buttons
				});
			}, this)
		});

		return false;
	};

	FolderListClass.prototype.getContainerWithExtAndIntLinks = function(params)
	{
		var internalLink = params.internalLink || '';
		var externalLink = params.externalLink || '';
		var objectId = params.objectId || '';

		if(objectId && !externalLink)
		{
			externalLink = this.getExternalLinkByObjectId(objectId);
		}
		var switchOnOffMessage = BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_OFF');
		if(externalLink)
		{
			switchOnOffMessage = BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON');
		}

		if(!this.containerWithExtAndIntLinks)
		{
			this.containerWithExtAndIntLinks = BX.create('div', {
				props: {
					className: 'bx-disk-sidebar-section'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'bx-disk-sidebar-shared-container'
						},
						children: [
							BX.create('div', {
								props: {
									className: 'bx-disk-sidebar-shared-title'
								},
								children: [
									BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK'),
									BX.create('a', {
										text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_PARAMS'),
										props: {
											id: 'bx-disk-sidebar-shared-param',
											href: '#',
											className: 'bx-disk-sidebar-shared-param'
										},
										events: {
											click: BX.proxy(this.openExternalLinkDetailSettings, this)
										}
									})
								]

							}),
							BX.create('div', {
								props: {
									className: 'bx-disk-sidebar-shared-outlink-container'
								},
								children: [
									BX.create('div', {
										props: {
											className: 'bx-disk-sidebar-shared-outlink-switcher-container'
										},
										events: {
											click: BX.proxy(this.onClickExternalSwitcherSidebar, this)
										},
										children: [
											BX.create('div', {
												props: {
													className: 'bx-disk-sidebar-shared-outlink-switcher-track ' + (externalLink? 'on' : 'off')
												},
												children: [
													BX.create('span', {
														props: {
															id: 'bx-disk-sidebar-shared-outlink-label'
														},
														text: switchOnOffMessage
													}),
													BX.create('div', {
														props: {
															className: 'bx-disk-sidebar-shared-outlink-switcher-point'
														}
													})
												]
											})
										]
									}),
									BX.create('div', {
										props: {
											className: 'bx-disk-sidebar-shared-outlink-input-container'
										},
										children: [
											BX.create('input', {
												props: {
													id: 'bx-disk-sidebar-shared-outlink-input',
													className: 'bx-disk-sidebar-shared-outlink-input',
													type: 'text',
													value: externalLink
												},
												events: {
													click: function(e)
													{
														BX.focus(this);
														this.setSelectionRange(0, this.value.length)
													}
												}
											})
										]
									})
								]
							})
						]
					})
					/*,
					BX.create('div', {
						props: {
							className: 'bx-disk-sidebar-shared-title'
						},
						text: BX.message('DISK_FOLDER_LIST_TITLE_SIDEBAR_INT_LINK')
					}),
					BX.create('div', {
						props: {
							className: 'bx-disk-sidebar-shared-inlink-container'
						},
						children: [
							BX.create('div', {
								props: {
									className: 'bx-disk-sidebar-shared-inlink-input-container'
								},
								children: [
									BX.create('div', {
										props: {
											className: 'bx-disk-sidebar-shared-inlink-input-container'
										},
										children: [
											BX.create('input', {
												props: {
													id: 'bx-disk-sidebar-shared-inlink-input',
													className: 'bx-disk-sidebar-shared-inlink-input',
													type: 'text',
													value: internalLink
												},
												events: {
													click: function(e)
													{
														BX.focus(this);
														this.setSelectionRange(0, this.value.length)
													}
												}
											})
										]
									})
								]
							})
						]
					})
					/**/
				]
			});
			return this.containerWithExtAndIntLinks;
		}
		//BX('bx-disk-sidebar-shared-inlink-input').value = internalLink;
		if(externalLink)
		{
			this.switchOnExtLinkInPanel({link: externalLink});
		}
		return this.containerWithExtAndIntLinks
	};

	var fix;
	FolderListClass.prototype.onScroll = function()
	{
		if(!BX.type.isFunction(fix))
		{
			fix = BX.throttle(function(){
				var counterWrap = BX("bx-disk-container", true);
				var panel = BX("disk_info_panel", true);
				if (counterWrap)
				{
					var top = counterWrap.getBoundingClientRect().top;
					if (top <= 0)
					{
						BX.style(panel.parentNode, 'paddingTop', (panel.offsetHeight + 30) + 'px');
						BX.addClass(panel, "attached-panel");
					}
					else
					{
						panel.parentNode.style.cssText = "";
						BX.removeClass(panel, "attached-panel");
					}
				}
			}, 150, this);
		}
		fix();
	};

	FolderListClass.prototype.onBeforeElementShow = function(viewer, element, status)
	{
		if(element.hasOwnProperty('image') || !BX.message('disk_restriction'))
		{
			return;
		}
		status.prevent = true;

		BX.PopupWindowManager.create('bx-disk-business-tools-info', null, {
			content: BX('bx-thurlyos-business-tools-info'),
			closeIcon: true,
			onPopupClose: function ()
			{
				this.destroy();
			},
			autoHide: true,
			zIndex: 11000
		}).show();
	};

	FolderListClass.prototype.onIframeElementLoadDataToView = function(element, responseData)
	{
		if(responseData && responseData.status === "restriction" && BX('bx-thurlyos-business-tools-info'))
		{
			if(BX.CViewer && BX.CViewer.objNowInShow)
			{
				if(element.currentModalWindow)
				{
					element.currentModalWindow.close();
				}

				BX.CViewer.objNowInShow.close();
			}
			BX.PopupWindowManager.create('bx-disk-business-tools-info', null, {
				content: BX('bx-thurlyos-business-tools-info'),
				closeIcon: true,
				onPopupClose: function ()
				{
					this.destroy();
				},
				autoHide: true,
				zIndex: 11000
			}).show();
		}
	};

	FolderListClass.prototype.onSelectRow = function(grid, selCount, checkbox)
	{
		this.addCheckbox(checkbox);
		if (selCount == 1) {
			this.showInfoPanelSingleObject(checkbox.value);
		}
		else
		{

			var checkboxes = this.grid.GetCheckedCheckboxes();
			var ids = [];
			for (var i in  checkboxes) {
				if (!checkboxes.hasOwnProperty(i) || checkboxes[i].name != 'ID[]') {
					continue;
				}
				ids.push(checkboxes[i].value);
			}

			this.showInfoPanelManyObject(ids);
		}
	};

	FolderListClass.prototype.onUnSelectRow = function(grid, selCount, checkbox)
	{
		this.deleteCheckbox(checkbox);
		if (selCount == 0) {
			var objectId = checkbox.value;
			var infoPanelContainer = BX('disk_info_panel');

			var child = BX.firstChild(infoPanelContainer);

			(new BX.easing({
				duration : 300,
				start : { opacity: 100, height : child.offsetHeight},
				finish : { opacity : 0, height : 0},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step : function(state) {
					infoPanelContainer.style.height = state.height + "px";
					infoPanelContainer.style.opacity = state.opacity / 100;
				},
				complete : BX.delegate(function() {
					infoPanelContainer.style.cssText = "";
					infoPanelContainer.style.display = "none";
					//BX.cleanNode(infoPanelContainer);
					var emptyContainer = BX('bx_disk_empty_select_section');
					if(emptyContainer)
						BX.show(emptyContainer, 'block');

				}, this)
			})).animate();
		}
		else
		{
			this.onSelectRow(grid, selCount, this.getFirstSelectedCheckbox());
		}
	};

	FolderListClass.prototype.getExternalLink = function (objectId)
	{
		var objectData = BX.delegate(getObjectDataId, this)(objectId);
		var isFolder = BX.hasClass(objectData.icon, 'bx-disk-folder-icon');
		var queryUrl = this.ajaxUrl;

		queryUrl = BX.Disk.addToLinkParam(queryUrl, 'action', 'generateExternalLink');
		queryUrl = BX.Disk.addToLinkParam(queryUrl, 'isFolder', isFolder);

		BX.Disk.modalWindowLoader(queryUrl, {
			id: 'bx-disk-external-link-loader',
			responseType: 'json',
			postData: {
				objectId: objectId
			},
			afterSuccessLoad: BX.delegate(function(response){

				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				this.cacheExternalLinks[objectId] = response.link;

				BX.Disk.modalWindow({
					modalId: 'bx-disk-external-link',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_GET_EXT_LINK'),
					contentClassName: 'tac',
					contentStyle: {
					},
					events: {
						onAfterPopupShow: function () {
							var inputExtLink = BX('disk-get-external-link');
							BX.focus(inputExtLink);
							inputExtLink.setSelectionRange(0, inputExtLink.value.length)
						},
						onPopupClose: function () {
							this.destroy();
						}
					},
					content: [
						BX.create('label', {
							props: {
								className: 'bx-disk-popup-label',
								"for": 'disk-get-external-link'
							}
						}),
						BX.create('input', {
							style: {
								marginTop: '10px'
							},
							props: {
								id: 'disk-get-external-link',
								className: 'bx-viewer-inp',
								type: 'text',
								value: response.link
							}
						})
					],
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('DISK_FOLDER_TOOLBAR_BTN_CLOSE'),
							events: {
								click: function () {
									BX.PopupWindowManager.getCurrentPopup().close();
								}
							}
						})
					]

				});
			}, this)
		});

		return false;
	};

	FolderListClass.prototype.getInternalLink = function (internalLink)
	{
		BX.Disk.modalWindow({
			modalId: 'bx-disk-internal-link',
			title: BX.message('DISK_FOLDER_LIST_ACT_COPY_INTERNAL_LINK'),
			contentClassName: 'tac',
			contentStyle: {
			},
			events: {
				onAfterPopupShow: function () {
					var inputLink = BX('disk-get-internal-link');
					BX.focus(inputLink);
					inputLink.setSelectionRange(0, inputLink.value.length)
				},
				onPopupClose: function () {
					this.destroy();
				}
			},
			content: [
				BX.create('label', {
					props: {
						className: 'bx-disk-popup-label',
						"for": 'disk-get-internal-link'
					}
				}),
				BX.create('input', {
					style: {
						marginTop: '10px'
					},
					props: {
						id: 'disk-get-internal-link',
						className: 'bx-viewer-inp',
						type: 'text',
						value: internalLink
					}
				})
			],
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message("DISK_FOLDER_TOOLBAR_BTN_CLOSE"),
					events: {
						click: function() {
							BX.PopupWindowManager.getCurrentPopup().close();
						}
					}
				})
			]
		});
	};

	FolderListClass.prototype.loadSubFolders = function (node)
	{
		if (!node) {
			return;
		}
		var objectId = node.getAttribute('data-object-id');
		if (!objectId) {
			return;
		}

		BX.Disk.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSubFoldersToAdd'),
			data: {
				objectId: objectId
			},
			onsuccess: BX.delegate(function (response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				this.buildTree(node, response);

			}, this)
		});
	};

	FolderListClass.prototype.buildTree = function (rootNode, response, ignoreNode)
	{
		ignoreNode = ignoreNode || {};
		if (!response || response.status != 'success') {
			BX.Disk.showModalWithStatusAction(response);
			return;
		}
		var ul = BX.create('ul', {
			props: {
				className: 'bx-disk-wood-folder'
			}
		});
		rootNode.appendChild(ul);
		if (response.items && response.items.length) {
			for (var i in response.items) {
				if (!response.items.hasOwnProperty(i)) {
					continue;
				}
				if(response.items[i].id == ignoreNode.id)
					continue;
				ul.appendChild(this.buildTreeNode(response.items[i]));
			}
		}
		else
		{
			var td = BX.findChild(rootNode, {
				className: 'bx-disk-wf-arrow'
			}, true);
			if(td)
			{
				BX.cleanNode(td);
			}
		}
		BX.removeClass(rootNode, 'bx-disk-close');
		BX.addClass(rootNode, 'bx-disk-open');
		BX.addClass(rootNode, 'bx-disk-loaded');
	};

	FolderListClass.prototype.buildTreeNode = function (object)
	{
		return BX.create('li', {
			props: {
				className: 'bx-disk-folder-container bx-disk-parent bx-disk-close ' + (!object.canAdd? 'bx-disk-only-view' : 'bx-disk-can-select')
			},
			attrs : {
				'data-object-id' : object.id,
				'data-can-add' : !!object.canAdd
			},
			children: [
				BX.create('div', {
					props: {
						className: 'bx-disk-folder-container'
					},
					children: [
						BX.create('table', {
							children: [
								BX.create('tr', {
									children: [
										BX.create('td', {
											props: {
												className: 'bx-disk-wf-arrow'
											},
											events: {
												click: BX.delegate(function (e) {
													var target = e.target || e.srcElement;
													var parent = BX.findParent(target, {
														className: 'bx-disk-parent'
													});
													if(BX.hasClass(parent, 'bx-disk-open'))
													{
														BX.removeClass(parent, 'bx-disk-open');
														BX.addClass(parent, 'bx-disk-close');
														return;
													}
													if(BX.hasClass(parent, 'bx-disk-loaded'))
													{
														BX.removeClass(parent, 'bx-disk-close');
														BX.addClass(parent, 'bx-disk-open');
														return;
													}
													this.loadSubFolders(parent);
												}, this)
											},
											children: [
												(object.hasSubFolders? BX.create('span') : null)
											]
										}),
										BX.create('td', {
											props: {
												className: 'bx-disk-wf-folder-icon'
											},
											events: {
												click: BX.delegate(function (e) {
													var target = e.target || e.srcElement;
													var parent = BX.findParent(target, {
														className: 'bx-disk-parent'
													});
													if(BX.hasClass(parent, 'selected'))
													{
														BX.removeClass(parent, 'selected');
														BX.onCustomEvent('onUnSelectFolder', [parent]);
														return;
													}
													BX.addClass(parent, 'selected');
													BX.onCustomEvent('onSelectFolder', [parent]);
												}, this)
											},
											children: [
												BX.create('span')
											]
										}),
										BX.create('td', {
											props: {
												className: 'bx-disk-wf-folder-name'
											},
											events: {
												click: BX.delegate(function (e) {
													var target = e.target || e.srcElement;
													var parent = BX.findParent(target, {
														className: 'bx-disk-parent'
													});
													if(BX.hasClass(parent, 'selected'))
													{
														BX.removeClass(parent, 'selected');
														BX.onCustomEvent('onUnSelectFolder', [parent]);
														return;
													}
													BX.addClass(parent, 'selected');
													BX.onCustomEvent('onSelectFolder', [parent]);
												}, this)
											},
											children: [
												BX.create('span', {
													text: object.name
												})
											]
										})
									]
								})
							]
						})
					]
				})
			]
		});
	};

	FolderListClass.prototype.openCopyModalWindow = function(rootObject, objectToMove)
	{
		var targetObjectId = null;
		var targetObjectNode = null;
		BX.addCustomEvent("onSelectFolder", function(node){
			if(!node.getAttribute('data-can-add'))
			{
				BX.removeClass(node, 'selected');
				return;
			}

			if(targetObjectNode)
			{
				BX.removeClass(targetObjectNode, 'selected');
			}
			targetObjectId = node.getAttribute('data-object-id');
			targetObjectNode = node;
		});
		BX.addCustomEvent("onUnSelectFolder", function(node){
			targetObjectId = null;
			targetObjectNode = null;
		});

		BX.Disk.modalWindowLoader(BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSubFoldersToAdd'), {
			id: 'bx-disk-copy-modal-loader',
			responseType: 'json',
			postData: {
				objectId: rootObject.id
			},
			afterSuccessLoad: BX.delegate(function(response) {
				if(!response || response.status != 'success')
				{
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				var rootNode = this.buildTreeNode(rootObject);
				var ul = BX.create('ul', {
					props: {
						className: 'bx-disk-wood-folder'
					}
				});
				rootNode.appendChild(ul);
				BX.Disk.modalWindow({
					modalId: 'bx-disk-copy-tree',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_TREE'),
					events: {
						onPopupClose: function () {
							this.destroy();
						}
					},
					content: [
						BX.create('div', {
							props: {
								className: 'bx-disk-popup-content-title'
							},
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO').replace('#NAME#', objectToMove.name)
						}),
						BX.create('ul', {
							props: {
								className: 'bx-disk-wood-folder'
							},
							children: [rootNode]
						})
					],
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO_BUTTON'),
							className: "webform-button-active",
							events: {
								click: BX.delegate(function (e) {
									if(!targetObjectId)
									{
										BX.PreventDefault(e);
										return false;
									}

									BX.PopupWindowManager.getCurrentPopup().close();
									BX.PreventDefault(e);

									BX.Disk.ajax({
										method: 'POST',
										dataType: 'json',
										url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'copyTo'),
										data: {
											objectId: objectToMove.id,
											targetObjectId: targetObjectId
										},
										onsuccess: function (data) {
											if (!data) {
												return;
											}
											if (data.status == 'success') {
												BX.Disk.showModalWithStatusAction(data);
												return;
											}
											BX.Disk.showModalWithStatusAction(data);
										}
									});

									return false;
								}, this)
							}
						})
					]
				});

				this.buildTree(rootNode, response);
			}, this)
		});
	};

	FolderListClass.prototype.openMoveModalWindow = function(rootObject, objectToMove)
	{
		var targetObjectId = null;
		var targetObjectNode = null;
		BX.addCustomEvent("onSelectFolder", function (node)
		{
			if(!node.getAttribute('data-can-add'))
			{
				BX.removeClass(node, 'selected');
				return;
			}

			if (targetObjectNode)
			{
				BX.removeClass(targetObjectNode, 'selected');
			}
			targetObjectId = node.getAttribute('data-object-id');
			targetObjectNode = node;
		});
		BX.addCustomEvent("onUnSelectFolder", function (node)
		{
			targetObjectId = null;
			targetObjectNode = null;
		});

		BX.Disk.modalWindowLoader(BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSubFoldersToAdd'), {
			id: 'bx-disk-move-modal-loader',
			responseType: 'json',
			postData: {
				objectId: rootObject.id
			},
			afterSuccessLoad: BX.delegate(function (response)
			{
				if (!response || response.status != 'success') {
					BX.Disk.showModalWithStatusAction(response);
					return;
				}
				var rootNode = this.buildTreeNode(rootObject);
				var ul = BX.create('ul', {
					props: {
						className: 'bx-disk-wood-folder'
					}
				});
				rootNode.appendChild(ul);
				BX.Disk.modalWindow({
					modalId: 'bx-disk-copy-tree',
					title: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_TREE'),
					events: {
						onPopupClose: function ()
						{
							this.destroy();
						}
					},
					content: [
						BX.create('div', {
							props: {
								className: 'bx-disk-popup-content-title'
							},
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO').replace('#NAME#', objectToMove.name)
						}),
						BX.create('ul', {
							props: {
								className: 'bx-disk-wood-folder'
							},
							children: [rootNode]
						})
					],
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO_BUTTON'),
							className: "webform-button-active",
							events: {
								click: BX.delegate(function (e)
								{
									if(!targetObjectId)
									{
										BX.PreventDefault(e);
										return false;
									}

									BX.PopupWindowManager.getCurrentPopup().close();
									BX.PreventDefault(e);

									BX.Disk.ajax({
										method: 'POST',
										dataType: 'json',
										url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'moveTo'),
										data: {
											objectId: objectToMove.id,
											targetObjectId: targetObjectId
										},
										onsuccess: BX.delegate(function (data)
										{
											if (!data) {
												return;
											}
											if (data.status == 'success') {
												this.removeRow(data.id);
												BX.Disk.showModalWithStatusAction(data);
												return;
											}
											BX.Disk.showModalWithStatusAction(data);
										}, this)
									});

									return false;
								}, this)
							}
						})
					]
				});

				this.buildTree(rootNode, response, objectToMove);
			}, this)
		});
	};


	var isChangedRights = false;
	var storageNewRights = {};
	var originalRights = {};
	var detachedRights = {};
	var moduleTasks = {};

	var entityToNewShared = {};
	var loadedReadOnlyEntityToNewShared = {};
	var entityToNewSharedMaxTaskName = '';

	FolderListClass.prototype.showSharingDetailWithChangeRights = function (params) {

		entityToNewShared = {};
		loadedReadOnlyEntityToNewShared = {};

		params = params || {};
		var objectId = params.object.id;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSharingDetailChangeRights'),
			{
				id: 'folder_list_sharing_detail_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					var objectOwner = {
						name: response.owner.name,
						avatar: response.owner.avatar,
						link: response.owner.link
					};

					BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-folder-change-right',
						title: BX.message('DISK_FOLDER_LIST_SHARING_TITLE_MODAL_2'),
						contentClassName: '',
						contentStyle: {
							//paddingTop: '30px',
							//paddingBottom: '70px'
						},
						events: {
							onAfterPopupShow: BX.delegate(function () {

								BX.addCustomEvent('onChangeRightOfSharing', BX.proxy(this.onChangeRightOfSharing, this));

								for (var i in response.members) {
									if (!response.members.hasOwnProperty(i)) {
										continue;
									}

									entityToNewShared[response.members[i].entityId] = {
										item: {
											id: response.members[i].entityId,
											name: response.members[i].name,
											avatar: response.members[i].avatar
										},
										type: response.members[i].type,
										right: response.members[i].right
									};
								}

								BX.SocNetLogDestination.init({
									name : this.destFormName,
									searchInput : BX('feed-add-post-destination-input'),
									bindMainPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									bindSearchPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									callback : {
										select : BX.proxy(this.onSelectDestination, this),
										unSelect : BX.proxy(this.onUnSelectDestination, this),
										openDialog : BX.proxy(this.onOpenDialogDestination, this),
										closeDialog : BX.proxy(this.onCloseDialogDestination, this),
										openSearch : BX.proxy(this.onOpenSearchDestination, this),
										closeSearch : BX.proxy(this.onCloseSearchDestination, this)
									},
									items: response.destination.items,
									itemsLast: response.destination.itemsLast,
									itemsSelected : response.destination.itemsSelected
								});

								var BXSocNetLogDestinationFormName = this.destFormName;
								BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);BX.PreventDefault(e); });
								BX.bind(BX('feed-add-post-destination-input'), 'keyup', BX.proxy(this.onKeyUpDestination, this));
								BX.bind(BX('feed-add-post-destination-input'), 'keydown', BX.proxy(this.onKeyDownDestination, this));

							}, this),
							onPopupClose: BX.delegate(function () {
								if(BX.SocNetLogDestination && BX.SocNetLogDestination.isOpenDialog())
								{
									BX.SocNetLogDestination.closeDialog()
								}
								BX.removeCustomEvent('onChangeRightOfSharing', BX.proxy(this.onChangeRightOfSharing, this));
								BX.proxy_context.destroy();
							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_OWNER') + '</td>' +
												'</tr>'
											}),
											BX.create('tr', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-col1" style="border-bottom: none;"><a class="bx-disk-filepage-used-people-link" href="' + objectOwner.link + '"><span class="bx-disk-filepage-used-people-avatar" style="background-image: url(' + objectOwner.avatar + ');"></span>' + BX.util.htmlspecialchars(objectOwner.name) + '</a></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									})
								]
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'changeSharingAndRights'),
											data: {
												objectId: objectId,
												entityToNewShared: entityToNewShared
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												BX.Disk.showModalWithStatusAction(response);
												var icon = BX.delegate(getIconElementByObjectId, this)(objectId);
												if(icon)
												{
													if(!entityToNewShared || BX.Disk.isEmptyObject(entityToNewShared))
													{
														BX.removeClass(icon, 'icon-shared icon-shared_2 shared');
														BX.removeClass(icon, 'icon-shared_1');
													}
													else
													{
														BX.addClass(icon, 'icon-shared icon-shared_2 shared');
													}
												}
											}, this)
										});

										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										BX.PopupWindowManager.getCurrentPopup().close();
									}
								}
							})
						]
					});
				}, this)
			}
		);
	};

	function showAccessCodeFullName(item)
	{
		item = item || {};

		return (item.provider? item.provider + ': ' : '') + item.name;
	}

	FolderListClass.prototype.showRights = function (params)
	{
		params = params || {};
		var objectId = params.object.id;
		var rights = {};

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showRightsDetail'),
			{
				id: 'folder_list_sharing_detail_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					for (var i in response.rights) {
						if (!response.rights.hasOwnProperty(i)) {
							continue;
						}
						var rightsByAccessCode = response.rights[i];
						for (var j in rightsByAccessCode) {
							if (!rightsByAccessCode.hasOwnProperty(j)) {
								continue;
							}

							rights[i] = {
								readOnly: true,
								item: {
									id: i,
									name: showAccessCodeFullName(response.accessCodeNames[i]),
									avatar: null
								},
								type: 'group',
								right: {
									title: rightsByAccessCode[j].TASK.TITLE
								}
							};
						}
					}

					BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-folder-change-right',
						title: BX.message('DISK_FOLDER_LIST_SHARING_TITLE_MODAL_2'),
						contentClassName: '',
						contentStyle: {
							//paddingTop: '30px',
							//paddingBottom: '70px'
						},
						events: {
							onAfterPopupShow: BX.delegate(function () {
								for (var i in rights) {
									if (!rights.hasOwnProperty(i)) {
										continue;
									}
									BX.Disk.appendRight(rights[i]);

								}

							}, this),
							onPopupClose: BX.delegate(function () {
								BX.proxy_context.destroy();
							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('a', {
										text: BX.message('DISK_FOLDER_TOOLBAR_BTN_CREATE_FOLDER'),
										props: {
											id: 'bx-disk-destination-object-modal',
											className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent border'
										},
										events: {
											click: BX.delegate(function () {
											}, this)
										},
										children: [
											BX.create('span', {
												props: {
													className: 'bx-disk-btn-icon bx-disk-btn-icon-plus'
												}
											}),
											BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER')
										]
									}),
									BX.create('div', {
										html:
												'<span class="feed-add-destination-input-box" id="feed-add-post-destination-input-box">' +
													'<input autocomplete="nope" type="text" value="" class="feed-add-destination-inp" id="feed-add-post-destination-input"/>' +
												'</span>'
									})
								]
							})
						],
						buttons: []
					});
				}, this)
			}
		);

	};

	FolderListClass.prototype.showRightsOnStorage = function ()
	{
		storageNewRights = {};
		var storageId = this.storage.id;
		var rights = {};

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showRightsOnStorageDetail'),
			{
				id: 'folder_list_rights_detail_storage_' + storageId,
				responseType: 'json',
				postData: {
					storageId: storageId
				},
				afterSuccessLoad: BX.delegate(function(response, windowLoader)
				{
					windowLoader && windowLoader.close();

					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					if(BX.Disk.isEmptyObject(moduleTasks))
					{
						moduleTasks = BX.clone(response.tasks, true);
						BX.Disk.setModuleTasks(moduleTasks);
					}

					for (var i in response.rights) {
						if (!response.rights.hasOwnProperty(i)) {
							continue;
						}
						var rightsByAccessCode = response.rights[i];
						for (var j in rightsByAccessCode) {
							if (!rightsByAccessCode.hasOwnProperty(j)) {
								continue;
							}

							rights[i] = {
								readOnly: !!rightsByAccessCode[j].READ_ONLY,
								item: {
									id: i,
									name: showAccessCodeFullName(response.accessCodeNames[i]),
									avatar: null
								},
								type: 'group',
								right: {
									title: rightsByAccessCode[j].TASK.TITLE,
									id: rightsByAccessCode[j].TASK.ID
								}
							};
						}
					}
					var showExtendedRights = !!response.showExtendedRights;
					var modalWindow = BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-folder-change-right',
						title: BX.message('DISK_FOLDER_LIST_RIGHTS_TITLE_MODAL'),
						withoutWindowManager: true,
						contentClassName: '',
						contentStyle: {
							//paddingTop: '30px',
							//paddingBottom: '70px'
						},
						events: {
							onAfterPopupShow: BX.delegate(function () {
								storageNewRights = BX.clone(rights, true);
								isChangedRights = false;

								BX.Access.Init({
									groups: { disabled: this.isThurlyOS }
								});
								var startValue = {};
								for (var key in storageNewRights) {
									if(!storageNewRights.hasOwnProperty(key))
										continue;

									storageNewRights[key].isThurlyOS = this.isThurlyOS;
									BX.Disk.appendSystemRight(storageNewRights[key]);
								}

								BX.addCustomEvent('onChangeSystemRight', BX.proxy(this.onChangeSystemRight, this));
								BX.addCustomEvent('onDetachSystemRight', BX.proxy(this.onDetachSystemRight, this));

								BX.bind(BX('feed-add-post-destination-container'), 'click', BX.delegate(function(e){
									var startValue = {};
									for (var key in storageNewRights) {
										if(!storageNewRights.hasOwnProperty(key))
											continue;
										startValue[key] = true;
									}
									BX.Access.SetSelected(startValue);


									BX.Access.ShowForm({
										showSelected: true,
										callback: BX.delegate(function (arRights){
											var res = [];
											for (var provider in arRights) {
												for (var id in arRights[provider]) {
													res.push(arRights[provider][id]);
													this.onSelectSystemRight(arRights[provider][id], provider);
												}
											}
										}, this)
									});

									return BX.PreventDefault(e);
								}, this));


							}, this),
							onPopupClose: BX.delegate(function () {

								BX.removeCustomEvent('onChangeSystemRight', BX.proxy(this.onChangeRight, this));

							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									}),
									BX.create('div', {
										style: {
											marginTop: '27px',
											marginBottom: '20px'
										},
										html: '<input type="checkbox" ' + (showExtendedRights? 'checked="checked"' : '') + ' id="showExtendedRights"/><label for="showExtendedRights">' + BX.message("DISK_FOLDER_LIST_LABEL_SHOW_EXTENDED_RIGHTS") + '</label>'
									})
								]
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'saveRightsOnStorage'),
											data: {
												isChangedRights: isChangedRights? 1 : 0,
												showExtendedRights: BX('showExtendedRights').checked? 1 : 0,
												storageId: storageId,
												storageNewRights: storageNewRights
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												BX.Disk.showModalWithStatusAction(response);
												document.location.reload();
											}, this)
										});

										if(!!modalWindow)
										{
											modalWindow.close();
										}
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										if(!!modalWindow)
										{
											modalWindow.close();
										}
									}
								}
							})
						]

					});
				}, this)
			}
		);

	};

	FolderListClass.prototype.showRightsOnObjectDetail = function (params)
	{
		storageNewRights = {};
		var storageId = this.storage.id;
		var rights = {};

		params = params || {};
		var objectId = params.object.id;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showRightsOnObjectDetail'),
			{
				id: 'folder_list_rights_detail_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId,
					storageId: storageId
				},
				afterSuccessLoad: BX.delegate(function(response, windowLoader)
				{
					windowLoader && windowLoader.close();

					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					if(BX.Disk.isEmptyObject(moduleTasks))
					{
						moduleTasks = BX.clone(response.tasks, true);
						BX.Disk.setModuleTasks(moduleTasks);
					}

					for (var i in response.rights) {
						if (!response.rights.hasOwnProperty(i)) {
							continue;
						}
						var rightsByAccessCode = response.rights[i];
						for (var j in rightsByAccessCode) {
							if (!rightsByAccessCode.hasOwnProperty(j)) {
								continue;
							}

							rights[i] = {
								item: {
									id: i,
									name: showAccessCodeFullName(response.accessCodeNames[i]),
									avatar: null
								},
								type: 'group',
								right: {
									title: rightsByAccessCode[j].TASK.TITLE,
									id: rightsByAccessCode[j].TASK.ID
								}
							};
						}
					}
					var modalWindow = BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-folder-change-right',
						title: BX.message('DISK_FOLDER_LIST_RIGHTS_TITLE_MODAL'),
						withoutWindowManager: true,
						contentClassName: '',
						contentStyle: {
							//paddingTop: '30px',
							//paddingBottom: '70px'
						},
						events: {
							onAfterPopupShow: BX.delegate(function () {
								storageNewRights = BX.clone(rights, true);
								originalRights = BX.clone(rights, true);
								detachedRights = {};

								BX.Access.Init({
									groups: { disabled: this.isThurlyOS }
								});
								for (var key in storageNewRights) {
									if(!storageNewRights.hasOwnProperty(key))
										continue;

									storageNewRights[key].isThurlyOS = this.isThurlyOS;
									BX.Disk.appendSystemRight(storageNewRights[key]);
								}

								BX.addCustomEvent('onChangeSystemRight', BX.proxy(this.onChangeSystemRight, this));
								BX.addCustomEvent('onDetachSystemRight', BX.proxy(this.onDetachSystemRight, this));

								BX.bind(BX('feed-add-post-destination-container'), 'click', BX.delegate(function(e){
									var startValue = {};
									for (var key in storageNewRights) {
										if(!storageNewRights.hasOwnProperty(key))
											continue;
										startValue[key] = true;
									}
									BX.Access.SetSelected(startValue);


									BX.Access.ShowForm({
										showSelected: true,
										callback: BX.delegate(function (arRights){
											var res = [];
											for (var provider in arRights) {
												for (var id in arRights[provider]) {
													res.push(arRights[provider][id]);
													this.onSelectSystemRight(arRights[provider][id], provider);
												}
											}
										}, this)
									});

									return BX.PreventDefault(e);
								}, this));


							}, this),
							onPopupClose: BX.delegate(function () {

								BX.removeCustomEvent('onChangeSystemRight', BX.proxy(this.onChangeRight, this));

							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									})
								]
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'saveRightsOnObject'),
											data: {
												objectId: objectId,
												objectNewRights: storageNewRights,
												detachedRights: detachedRights
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												BX.Disk.showModalWithStatusAction(response);

											}, this)
										});

										if(!!modalWindow)
										{
											modalWindow.close();
										}
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										if(!!modalWindow)
										{
											modalWindow.close();
										}
									}
								}
							})
						]

					});
				}, this)
			}
		);

	};

	FolderListClass.prototype.showSettingsOnBizproc = function ()
	{
		var storageId = this.storage.id;
		var activationBizProc = '';

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSettingsOnBizproc'),
			{
				responseType: 'json',
				postData: {
					storageId: storageId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					if(response.statusBizProc)
					{
						activationBizProc = 'checked';
					}

					BX.Disk.modalWindow({
						modalId: 'bx-disk-settings-bizproc',
						title: BX.message('DISK_FOLDER_LIST_BIZPROC_TITLE_MODAL'),
						contentClassName: '',
						events: {
						},
						content: [
							BX.create('table', {
								html: '<tr><td><label for="activationBizProc">'+BX.message("DISK_FOLDER_LIST_BIZPROC_LABEL")+'</label></td>' +
								'<td><input type="checkbox" id="activationBizProc" '+activationBizProc+' /></td>' +
								'</tr>'
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'saveSettingsOnBizproc'),
											data: {
												storageId: storageId,
												activationBizproc: BX('activationBizProc').checked ? 1 : 0
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												if(response.status != 'success')
												{
													response.errors = response.errors || [{}];
													BX.Disk.showModalWithStatusAction({
														status: 'error',
														message: response.errors.pop().message
													})
												}
												else
												{
													BX.Disk.showModalWithStatusAction(response);
												}
												location.reload();
											}, this)
										});
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										BX.PopupWindowManager.getCurrentPopup().close();
									}
								}
							})
						]

					});
				}, this)
			}
		);

	};

	FolderListClass.prototype.openWindowForSelectDocumentService = function (params) {
		var viewer = new BX.CViewer({});
		viewer.openWindowForSelectDocumentService({viewInUf: false});
	};

	FolderListClass.prototype.showHiddenContent = function (el)
	{
		el.style.display = (el.style.display == 'none') ? 'block' : 'none';
	};

	FolderListClass.prototype.hide = function(el)
	{
		if (!el.getAttribute('displayOld'))
		{
			el.setAttribute("displayOld", el.style.display)
		}
		el.style.display = "none"
	};

	FolderListClass.prototype.showNetworkDriveConnect = function (params)
	{
		params = params || {};
		var link = params.link,
			showHiddenContent = this.showHiddenContent,
			hide = this.hide;
		showHiddenContent(BX('bx-disk-network-drive-full'));

		BX.Disk.modalWindow({
			modalId: 'bx-disk-show-network-drive-connect',
			title: BX.message('DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE'),
			contentClassName: 'tac',
			contentStyle: {
			},
			events: {
				onAfterPopupShow: function () {
					var inputLink = BX('disk-get-network-drive-link');
					BX.focus(inputLink);
					inputLink.setSelectionRange(0, inputLink.value.length)
				},
				onPopupClose: function () {
					hide(BX('bx-disk-network-drive'));
					hide(BX('bx-disk-network-drive-full'));
					document.body.appendChild(BX('bx-disk-network-drive-full'));
					this.destroy();
				}
			},
			content: [
				BX.create('label', {
					text: BX.message('DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE_DESCR_MODAL') + ' :',
					props: {
						className: 'bx-disk-popup-label',
						"for": 'disk-get-network-drive-link'
					}
				}),
				BX.create('input', {
					style: {
						marginTop: '10px'
					},
					props: {
						id: 'disk-get-network-drive-link',
						className: 'bx-disk-popup-input',
						type: 'text',
						value: link
					}
				}),
				BX('bx-disk-network-drive-full')
			],
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message('DISK_FOLDER_TOOLBAR_BTN_CLOSE'),
					events: {
						click: function () {
							BX.PopupWindowManager.getCurrentPopup().close();
						}
					}
				})
			]
		});
		if(BX('bx-disk-network-drive-secure-label'))
		{
			hide(BX.findChildByClassName(BX('bx-disk-show-network-drive-connect'), 'bx-disk-popup-label'));
			hide(BX.findChildByClassName(BX('bx-disk-show-network-drive-connect'), 'bx-disk-popup-input'));
		}
	};


	FolderListClass.prototype.showSharingDetailWithSharing = function (params) {

		entityToNewShared = {};
		loadedReadOnlyEntityToNewShared = {};

		params = params || {};
		var objectId = params.object.id;

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showSharingDetailAppendSharing'),
			{
				id: 'folder_list_sharing_detail_object_' + objectId,
				responseType: 'json',
				postData: {
					objectId: objectId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					var objectOwner = {
						name: response.owner.name,
						avatar: response.owner.avatar,
						link: response.owner.link
					};
					entityToNewSharedMaxTaskName = response.owner.maxTaskName;

					BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-folder-change-right',
						title: BX.message('DISK_FOLDER_LIST_SHARING_TITLE_MODAL_2'),
						contentClassName: '',
						contentStyle: {
							//paddingTop: '30px',
							//paddingBottom: '70px'
						},
						events: {
							onAfterPopupShow: BX.delegate(function () {

								BX.addCustomEvent('onChangeRightOfSharing', BX.proxy(this.onChangeRightOfSharing, this));

								for (var i in response.members) {
									if (!response.members.hasOwnProperty(i)) {
										continue;
									}

									entityToNewShared[response.members[i].entityId] = {
										item: {
											id: response.members[i].entityId,
											name: response.members[i].name,
											avatar: response.members[i].avatar
										},
										type: response.members[i].type,
										right: response.members[i].right
									};
								}
								loadedReadOnlyEntityToNewShared = BX.clone(entityToNewShared, true);

								BX.SocNetLogDestination.init({
									name : this.destFormName,
									searchInput : BX('feed-add-post-destination-input'),
									bindMainPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									bindSearchPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									callback : {
										select : BX.proxy(this.onSelectDestination, this),
										unSelect : BX.proxy(this.onUnSelectDestination, this),
										openDialog : BX.proxy(this.onOpenDialogDestination, this),
										closeDialog : BX.proxy(this.onCloseDialogDestination, this),
										openSearch : BX.proxy(this.onOpenSearchDestination, this),
										closeSearch : BX.proxy(this.onCloseSearchDestination, this)
									},
									items: response.destination.items,
									itemsLast: response.destination.itemsLast,
									itemsSelected : response.destination.itemsSelected
								});

								var BXSocNetLogDestinationFormName = this.destFormName;
								BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);BX.PreventDefault(e); });
								BX.bind(BX('feed-add-post-destination-input'), 'keyup', BX.proxy(this.onKeyUpDestination, this));
								BX.bind(BX('feed-add-post-destination-input'), 'keydown', BX.proxy(this.onKeyDownDestination, this));
							}, this),
							onPopupClose: BX.delegate(function () {
								if(BX.SocNetLogDestination && BX.SocNetLogDestination.isOpenDialog())
								{
									BX.SocNetLogDestination.closeDialog()
								}

								BX.removeCustomEvent('onChangeRightOfSharing', BX.proxy(this.onChangeRightOfSharing, this));
								BX.proxy_context.destroy();
							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_OWNER') + '</td>' +
												'</tr>'
											}),
											BX.create('tr', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-col1" style="border-bottom: none;"><a class="bx-disk-filepage-used-people-link" href="' + objectOwner.link + '"><span class="bx-disk-filepage-used-people-avatar" style="background-image: url(' + objectOwner.avatar + ');"></span>' + BX.util.htmlspecialchars(objectOwner.name) + '</a></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									})
								]
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'appendSharing'),
											data: {
												objectId: objectId,
												entityToNewShared: entityToNewShared
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												BX.Disk.showModalWithStatusAction(response);
												var icon = BX.delegate(getIconElementByObjectId, this)(objectId);
												if(icon)
												{
													if(!entityToNewShared || BX.Disk.isEmptyObject(entityToNewShared))
													{
														BX.removeClass(icon, 'icon-shared icon-shared_2 shared');
														BX.removeClass(icon, 'icon-shared_1');
													}
													else
													{
														BX.addClass(icon, 'icon-shared icon-shared_2 shared');
													}
												}

											}, this)
										});

										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										BX.PopupWindowManager.getCurrentPopup().close();
									}
								}
							})
						]
					});
				}, this)
			}
		);
	};

	FolderListClass.prototype.onCreateExtendedFolder = function () {
		this.showCreateFolderWithSharing({

		});
	};

	FolderListClass.prototype.showCreateFolderWithSharing = function ()
	{
		entityToNewShared = {};
		storageNewRights = {};
		var storageId = this.storage.id;
		var rights = {};

		BX.Disk.modalWindowLoader(
			BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'showCreateFolderWithSharingInCommon'),
			{
				id: 'folder_list_rights_detail_storage_' + storageId,
				responseType: 'json',
				postData: {
					storageId: storageId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Disk.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						})
					}

					if(BX.Disk.isEmptyObject(moduleTasks))
					{
						moduleTasks = BX.clone(response.tasks, true);
						BX.Disk.setModuleTasks(moduleTasks);
					}

					for (var i in response.rights) {
						if (!response.rights.hasOwnProperty(i)) {
							continue;
						}
						var rightsByAccessCode = response.rights[i];
						for (var j in rightsByAccessCode) {
							if (!rightsByAccessCode.hasOwnProperty(j)) {
								continue;
							}

							rights[i] = {
								detachOnly: true,
								item: {
									id: i,
									name: response.accessCodeNames[i].name,
									avatar: null
								},
								type: 'group',
								right: {
									title: rightsByAccessCode[j].TASK.TITLE,
									id: rightsByAccessCode[j].TASK.ID
								}
							};
						}
					}

					BX.Disk.modalWindow({
						modalId: 'bx-disk-detail-sharing-create-folder',
						title: BX.message('DISK_FOLDER_LIST_CREATE_FOLDER_MODAL'),
						contentClassName: '',
						contentStyle: {},
						events: {
							onAfterPopupShow: BX.delegate(function () {
								BX.focus(BX('disk-new-create-filename'));
								storageNewRights = BX.clone(rights, true);

								for (var i in rights) {
									if (!rights.hasOwnProperty(i)) {
										continue;
									}
									BX.Disk.appendRight(rights[i]);

								}


								BX.addCustomEvent('onChangeRightOfSharing', BX.proxy(this.onChangeRightOfSharing, this));
								BX.addCustomEvent('onChangeRight', BX.proxy(this.onChangeRight, this));
								BX.addCustomEvent('onDetachRight', BX.proxy(this.onDetachRight, this));

								BX.SocNetLogDestination.init({
									name : this.destFormName,
									searchInput : BX('feed-add-post-destination-input'),
									bindMainPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									bindSearchPopup : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
									callback : {
										select : BX.proxy(this.onSelectDestination, this),
										unSelect : BX.proxy(this.onUnSelectDestination, this),
										openDialog : BX.proxy(this.onOpenDialogDestination, this),
										closeDialog : BX.proxy(this.onCloseDialogDestination, this),
										openSearch : BX.proxy(this.onOpenSearchDestination, this),
										closeSearch : BX.proxy(this.onCloseSearchDestination, this)
									},
									items: response.destination.items,
									itemsLast: response.destination.itemsLast,
									itemsSelected : response.destination.itemsSelected
								});

								var BXSocNetLogDestinationFormName = this.destFormName;
								BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);BX.PreventDefault(e); });
								BX.bind(BX('feed-add-post-destination-input'), 'keyup', BX.proxy(this.onKeyUpDestination, this));
								BX.bind(BX('feed-add-post-destination-input'), 'keydown', BX.proxy(this.onKeyDownDestination, this));



							}, this),
							onPopupClose: BX.delegate(function () {
								if(BX.SocNetLogDestination && BX.SocNetLogDestination.isOpenDialog())
								{
									BX.SocNetLogDestination.closeDialog()
								}

								BX.removeCustomEvent('onChangeRight', BX.proxy(this.onChangeRight, this));
								BX.proxy_context.destroy();
							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content-small'
								},
								children: [
									BX.create('label', {
										props: {
											className: 'bx-disk-popup-label',
											"for": 'disk-new-create-filename'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'req'
												},
												text: '*'
											}),
											BX.message('DISK_FOLDER_LIST_LABEL_NAME_CREATE_FOLDER')
										]
									}),
									BX.create('input', {
										props: {
											id: 'disk-new-create-filename',
											className: 'bx-disk-popup-input',
											type: 'text',
											value: ''
										},
										style: {
											fontSize: '16px',
											marginTop: '10px'
										}
									})
								]
							}),
							BX.create('div', {
								props: {
									className: 'bx-disk-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											id: 'bx-disk-popup-shared-people-list',
											className: 'bx-disk-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
													'<td class="bx-disk-popup-shared-people-list-head-col1">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col2">' + BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') + '</td>' +
													'<td class="bx-disk-popup-shared-people-list-head-col3"></td>' +
												'</tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									})
								]
							})
						],
						buttons: [
							new BX.PopupWindowButton({
								text: BX.message('DISK_FOLDER_LIST_BTN_SAVE'),
								className: "popup-window-button-accept",
								events: {
									click: BX.delegate(function () {
										var newName = BX('disk-new-create-filename').value;
										if (!newName) {
											BX.focus(BX('disk-new-create-filename'));
											return;
										}

										BX.Disk.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Disk.addToLinkParam(this.ajaxUrl, 'action', 'createFolderWithSharing'),
											data: {
												name: newName,
												storageId: storageId,
												storageNewRights: storageNewRights || {},
												entityToNewShared: entityToNewShared || {}
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) {
													return;
												}
												BX.Disk.showModalWithStatusAction(response);
												if (response.status && response.status == 'success') {
													window.document.location = BX.Disk.getUrlToShowObjectInGrid(response.folder.id);
												}
											}, this)
										});

										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('DISK_FOLDER_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-disk-btn bx-disk-btn-big bx-disk-btn-transparent'
								},
								events: {
									click: function () {
										BX.PopupWindowManager.getCurrentPopup().close();
									}
								}
							})
						]
					});
				}, this)
			}
		);

	};

	FolderListClass.prototype.onSelectSystemRight = function(item, type)
	{
		storageNewRights[item.id] = storageNewRights[item.id] || {};
		isChangedRights = true;

		var providerPrefix = BX.Access.GetProviderPrefix(type, item.id);
		storageNewRights[item.id] = {
			item: {
				avatar: null,
				id: item.id,
				name: (providerPrefix? providerPrefix + ': ': '') + item.name
			},
			type: 'user', //todo fix nd actualize this. May be groups, users, departments, etc.
			right: 'read'
		};

		storageNewRights[item.id].isThurlyOS = this.isThurlyOS;
		BX.Disk.appendSystemRight(storageNewRights[item.id]);
	};

	FolderListClass.prototype.onSelectRightDestination = function(item, type, search)
	{
		storageNewRights[item.id] = storageNewRights[item.id] || {};

		storageNewRights[item.id] = {
			item: item,
			type: type,
			right: storageNewRights[item.id].right || {}
		};

		BX.Disk.appendRight({
			destFormName: this.destFormName,
			item: item,
			type: type,
			right: storageNewRights[item.id].right
		});
	};

	FolderListClass.prototype.onUnSelectRightDestination = function (item, type, search)
	{
		var entityId = item.id;

		delete storageNewRights[entityId];

		var child = BX.findChild(BX('bx-disk-popup-shared-people-list'), {attribute: {'data-dest-id': '' + entityId + ''}}, true);
		if (child) {
			BX.remove(child);
		}
	};

	FolderListClass.prototype.onChangeSystemRight = function(entityId, task)
	{
		if(storageNewRights[entityId])
		{
			isChangedRights = true;
			storageNewRights[entityId].right = {
				id: task.ID,
				title: task.TITLE
			};
		}
	};

	FolderListClass.prototype.onDetachSystemRight = function(entityId)
	{
		if(storageNewRights[entityId])
		{
			isChangedRights = true;
			BX.Access.DeleteSelected(entityId);
			detachedRights[entityId] = storageNewRights[entityId];

			delete storageNewRights[entityId];
		}
	};

	FolderListClass.prototype.onChangeRight = function(entityId, task)
	{
		if(storageNewRights[entityId])
		{
			storageNewRights[entityId].right = {
				id: task.ID,
				title: task.TITLE
			};
		}
	};

	FolderListClass.prototype.onDetachRight = function(entityId)
	{
		if(storageNewRights[entityId])
		{
			delete storageNewRights[entityId];
		}
	};

	FolderListClass.prototype.onSelectDestination = function(item, type, search)
	{
		entityToNewShared[item.id] = entityToNewShared[item.id] || {};
		BX.Disk.appendNewShared({
			maxTaskName: entityToNewSharedMaxTaskName,
			readOnly: !!loadedReadOnlyEntityToNewShared[item.id],
			destFormName: this.destFormName,
			item: item,
			type: type,
			right: entityToNewShared[item.id].right
		});

		entityToNewShared[item.id] = {
			item: item,
			type: type,
			right: entityToNewShared[item.id].right || 'disk_access_read'
		};
	};

	FolderListClass.prototype.onUnSelectDestination = function (item, type, search)
	{
		var entityId = item.id;

		if(!!loadedReadOnlyEntityToNewShared[entityId])
		{
			return false;
		}

		delete entityToNewShared[entityId];

		var child = BX.findChild(BX('bx-disk-popup-shared-people-list'), {attribute: {'data-dest-id': '' + entityId + ''}}, true);
		if (child) {
			BX.remove(child);
		}
	};

	FolderListClass.prototype.onChangeRightOfSharing = function(entityId, taskName)
	{
		if(entityToNewShared[entityId])
		{
			entityToNewShared[entityId].right = taskName;
		}
	};

	FolderListClass.prototype.onOpenDialogDestination = function()
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'inline-block');
		BX.style(BX('bx-destination-tag'), 'display', 'none');
		BX.focus(BX('feed-add-post-destination-input'));
		if(BX.SocNetLogDestination.popupWindow)
			BX.SocNetLogDestination.popupWindow.adjustPosition({ forceTop: true });
	};

	FolderListClass.prototype.onCloseDialogDestination = function()
	{
		var input = BX('feed-add-post-destination-input');
		if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length <= 0)
		{
			BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
			BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		}
	};

	FolderListClass.prototype.onOpenSearchDestination = function()
	{
		if(BX.SocNetLogDestination.popupSearchWindow)
			BX.SocNetLogDestination.popupSearchWindow.adjustPosition({ forceTop: true });
	};

	FolderListClass.prototype.onCloseSearchDestination = function()
	{
		var input = BX('feed-add-post-destination-input');
		if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length > 0)
		{
			BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
			BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
			BX('feed-add-post-destination-input').value = '';
		}
	};

	FolderListClass.prototype.onKeyDownDestination = function (event)
	{
		var BXSocNetLogDestinationFormName = this.destFormName;
		if (event.keyCode == 8 && BX('feed-add-post-destination-input').value.length <= 0) {
			BX.SocNetLogDestination.sendEvent = false;
			BX.SocNetLogDestination.deleteLastItem(BXSocNetLogDestinationFormName);
		}

		return true;
	};

	FolderListClass.prototype.onKeyUpDestination = function (event)
	{
		var BXSocNetLogDestinationFormName = this.destFormName;
		if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
			return false;

		if (event.keyCode == 13) {
			BX.SocNetLogDestination.selectFirstSearchItem(BXSocNetLogDestinationFormName);
			return BX.PreventDefault(event);
		}
		if (event.keyCode == 27) {
			BX('feed-add-post-destination-input').value = '';
		}
		else {
			BX.SocNetLogDestination.search(BX('feed-add-post-destination-input').value, true, BXSocNetLogDestinationFormName);
		}

		if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
			BX.SocNetLogDestination.closeDialog();

		if (event.keyCode == 8) {
			BX.SocNetLogDestination.sendEvent = true;
		}
		return BX.PreventDefault(event);
	};


	return FolderListClass;
})();