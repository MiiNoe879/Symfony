BX.namespace("Crm.KanbanComponent");

BX.Crm.KanbanComponent.currentPopupItem = null;
BX.Crm.KanbanComponent.currentPopup = null;
BX.Crm.KanbanComponent.currentData = null;
BX.Crm.KanbanComponent.successClosePopup = false;
BX.Crm.KanbanComponent.dropConfirmed = false;

/**
 * Return item to the last position and dec/inc price in column.
 * @param {BX.Kanban.Item} item
 * @returns {void}
 */
BX.Crm.KanbanComponent.returnItem = function(item)
{
	var lastPosition = item.getLastPosition();
	var data = item.getData();
	var grid = item.getGrid();
	var price = parseFloat(data.price);

	data.columnId = lastPosition.columnId;
	data.targetId = lastPosition.targetId;
	
	// dec in column and inc in last column
	item.getColumn().decPrice(price);
	grid.getColumn(data.columnId).incPrice(price);
	// update item info
	grid.updateItem(item.getId(), data);
	grid.unhideItem(item);
};

/**
 * Clear popup form.
 * @param {String} containerId DOM id of popup content.
 * @returns {void}
 */
BX.Crm.KanbanComponent.clearPopup = function(containerId)
{
	var fields = BX.findChild(BX(containerId), {
		className: "crm-kanban-popup-field"
	}, true, true);

	if (fields && fields.length > 0)
	{
		for (var i = 0, c = fields.length; i < c; i++)
		{
			var defaultVal = BX.data(fields[i], "default");
			fields[i].value = defaultVal ? defaultVal : "";
		}
	}
};

/**
 * Get all fields from popup for send to the backend.
 * @param {String} containerId DOM id of popup content.
 * @returns {array}
 */
BX.Crm.KanbanComponent.collectFieldsPopup = function(containerId)
{
	var fields = BX.findChild(BX(containerId), {
		className: "crm-kanban-popup-field"
	}, true, true);
	var post = {};

	if (fields && fields.length > 0)
	{
		for (var i = 0, c = fields.length; i < c; i++)
		{
			var name = BX.data(fields[i], "field");
			if (name)
			{
				post[name] = fields[i].value;
			}
		}
	}

	return post;
};

/**
 * Show some popup.
 * @param {String} containerId DOM id of popup content.
 * @param {Object} handlerData Data from handler.
 * @param {String type handlerType Column or DropZone.
 * @returns {void}
 */
BX.Crm.KanbanComponent.showPopup = function(containerId, handlerData, handlerType)
{
	BX.Crm.KanbanComponent.currentPopupItem = handlerData.item;
	this.currentPopup = new BX.PopupWindow(
		"kanban_column_popup",
		window.body,
		{
			closeIcon : true,
			offsetLeft : 0,
			lightShadow : true,
			overlay : true,
			titleBar: {content: BX.create("span", {html: ""})},
			draggable: true,
			contentColor: "white",
			closeByEsc : true,
			events: {
				onPopupClose: function()
				{
					if (!this.successClosePopup)
					{
						this.returnItem(handlerData.item);
					}
					this.dropConfirmed = false;
				}.bind(this)
			},
			buttons: [
				// if ok, set data to backend
				containerId !== "crm_kanban_lead_win"
				? new BX.PopupWindowButton(
					{
						text: BX.message("CRM_KANBAN_POPUP_PARAMS_SAVE"),
						className: "popup-window-button-accept",
						events:
						{
							click: function()
							{
								if (handlerType.toLowerCase() === "column")
								{
									var grid = handlerData.item.getGrid();
									
									grid.setAjaxParams(
										this.collectFieldsPopup(containerId)
									);
									grid.onItemMoved(
										handlerData.item,
										handlerData.targetColumn,
										handlerData.beforeItem,
										true
									);
									this.successClosePopup = true;
								}
								else if (handlerType.toLowerCase() === "dropzone")
								{
									var item = handlerData.getItem();
									var grid = item.getGrid();
									var dropZone = handlerData.getDropZone();
									
									grid.setAjaxParams(
										this.collectFieldsPopup(containerId)
									);
									grid.unhideItem(item);
									dropZone.captureItem(item);
									this.successClosePopup = true;
								}
								this.currentPopup.close();
							}.bind(this)
						}
					}
				)
				: null,
				// if decline, return item to the last position
				new BX.PopupWindowButton(
					{
						text: BX.message("CRM_KANBAN_POPUP_PARAMS_CANCEL"),
						className: "popup-window-button-decline",
						events:
						{
							click: function()
							{
								this.returnItem(handlerData.item);
								this.currentPopup.close();
							}.bind(this)
						}
					}
				)
			]
		}
	);
	this.clearPopup(containerId);
	this.currentPopup.setContent(BX(containerId));
	this.currentPopup.setTitleBar(BX.data(BX(containerId), "title"));
	this.currentPopup.show();
};

/**
 * Hook on select schema of convert lead.
 * @param {String} schema Selected schema.
 * @returns {void}
 */
BX.Crm.KanbanComponent.leadConvert = function(schema)
{
	var data = this.currentData;

	if (!data || !data.item)
	{
		return;
	}

	var id = data.item.getId();
	
	this.successClosePopup = true;
	this.currentPopup.close();

	if (schema === "SELECT")
	{
		BX.CrmLeadConverter.getCurrent().openEntitySelector
		(
			function(result)
			{
				BX.Crm.KanbanComponent.currentPopupItem = null;
				BX.CrmLeadConverter.getCurrent().convert(
					id,
					result.config,
					"",
					result.data
				);
			}
		);
	}
	else
	{
		BX.CrmLeadConverter.getCurrent().convert(
			id, 
			BX.CrmLeadConversionScheme.createConfig(schema), 
			""
		);
	}
};

/**
 * Handler on drop item to the column.
 * @param {Object} data Data from handler.
 * @returns {void}
 */
BX.Crm.KanbanComponent.columnPopup = function(data)
{
	var grid = data.grid;
	var gridData = grid.getData();
	var item = data.item;
	var itemData = item.getData();
	var targetColumn = data.targetColumn;
	var targetColumnId = targetColumn ? targetColumn.getId() : 0;

	BX.Crm.KanbanComponent.currentData = data;

	if (targetColumn && targetColumnId !== itemData.columnId)
	{
		var columnData = targetColumn.getData();

		// show popup on lead
		if (
			columnData.type === "WIN" && 
			gridData.entityType === "LEAD"
		)
		{
			BX.Crm.KanbanComponent.showPopup("crm_kanban_lead_win", data, "column");
			data.skip = true;
		}
		// on invoice
		else if (gridData.entityType === "INVOICE")
		{
			if (columnData.type === "WIN")
			{
				BX.Crm.KanbanComponent.showPopup("crm_kanban_invoice_win", data, "column");
				data.skip = true;
			}
			else if (columnData.type === "LOOSE")
			{
				BX.Crm.KanbanComponent.showPopup("crm_kanban_invoice_loose", data, "column");
				data.skip = true;
			}
		}
	}
};

/**
 * Handler on drop item to the dropZone. 
 * @param {BX.CRM.Kanban.Grid} grid
 * @param {BX.Kanban.DropZoneEvent} dropEvent
 * @returns {void}
 */
BX.Crm.KanbanComponent.dropPopup = function(grid, dropEvent)
{
	var gridData = grid.getData();
	var dropZone = dropEvent.getDropZone();
	var dropZoneData = dropZone.getData();
	var item = dropEvent.getItem();
	
	// for second handler must return
	if (BX.Crm.KanbanComponent.dropConfirmed !== false)
	{
		return;
	}
	else
	{
		BX.Crm.KanbanComponent.dropConfirmed = true;
	}
	
	// show popup on lead
	if (gridData.entityType === "LEAD")
	{
		if (dropZoneData.type === "WIN")
		{
			grid.hideItem(item);
			BX.Crm.KanbanComponent.currentData = {
				grid: grid,
				item: item,
				targetColumn: null,
				beforeItem: null
			};
			BX.Crm.KanbanComponent.showPopup("crm_kanban_lead_win", dropEvent, "dropzone");
			dropEvent.denyAction();
		}
	}
	// show popup on invoice
	else if (gridData.entityType === "INVOICE")
	{
		grid.hideItem(item);
		BX.Crm.KanbanComponent.currentData = {
			grid: grid,
			item: item,
			targetColumn: null,
			beforeItem: null
		};
		if (dropZoneData.type === "WIN")
		{
			BX.Crm.KanbanComponent.showPopup("crm_kanban_invoice_win", dropEvent, "dropzone");
		}
		else
		{
			BX.Crm.KanbanComponent.showPopup("crm_kanban_invoice_loose", dropEvent, "dropzone");
		}
		dropEvent.denyAction();
	}
};

/**
 * On popup close.
 * @param {BX.PopupWindow} popupWindow Instance of popup.
 * @returns {void}
 */
BX.Crm.KanbanComponent.onPopupClose = function(popupWindow)
{
	// detect lead converter cancel (second step)
	if (
		popupWindow.uniquePopupId === "CRM-lead_converter-popup" &&
		BX.Crm.KanbanComponent.currentPopupItem !== null
	)
	{
		setTimeout(function()
		{
			if (BX.Crm.KanbanComponent.currentPopupItem !== null)
			{
				BX.Crm.KanbanComponent.returnItem(BX.Crm.KanbanComponent.currentPopupItem);
			}
		}, 300);
	}
};