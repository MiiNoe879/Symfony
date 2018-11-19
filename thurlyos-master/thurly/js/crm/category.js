BX.namespace("BX.Crm");

if(typeof(BX.Crm.DealCategoryChanger) === "undefined")
{
	BX.Crm.DealCategoryChanger = function()
	{
		this._id = "";
		this._settings = null;
		this._selector = null;
		this._selectListener = BX.delegate(this.onSelect, this);
		this._serviceUrl = "";
		this._entityId = 0;

		this._confirmationDialog = null;
		this._errorDialog = null;
	};

	BX.Crm.DealCategoryChanger.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};
			this._serviceUrl = BX.prop.getString(this._settings, "serviceUrl", "");
			this._entityId = BX.prop.getInteger(this._settings, "entityId", 0);
		},
		getId: function()
		{
			return this._id;
		},
		getMessage: function(name)
		{
			return BX.prop.getString(BX.Crm.DealCategoryChanger.messages, name, name);
		},
		process: function()
		{
			this.openConfirmationDialog(
				BX.delegate(function(){ this.closeConfirmationDialog(); this.openSelector(); }, this),
				BX.delegate(function(){ this.closeConfirmationDialog(); }, this)
			);
		},
		openConfirmationDialog: function(onConfirm, onCancel)
		{
			this._confirmationDialog = new BX.PopupWindow(
				this._id + "_confirm",
				null,
				{
					autoHide: false,
					draggable: true,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: { top: "10px", right: "15px" },
					zIndex: 0,
					titleBar: this.getMessage("dialogTitle"),
					content: this.getMessage("dialogSummary"),
					className : "crm-text-popup",
					lightShadow : true,
					buttons:
					[
						new BX.PopupWindowButton(
							{
								text : BX.message("JS_CORE_WINDOW_CONTINUE"),
								className : "popup-window-button-accept",
								events: { click: onConfirm }
							}
						),
						new BX.PopupWindowButtonLink(
							{
								text : BX.message("JS_CORE_WINDOW_CANCEL"),
								className : "popup-window-button-link-cancel",
								events: { click: onCancel }
							}
						)
					]
				}
			);
			this._confirmationDialog.show();
		},
		closeConfirmationDialog: function()
		{
			if(this._confirmationDialog)
			{
				this._confirmationDialog.close();
				this._confirmationDialog.destroy();
				this._confirmationDialog = null;
			}
		},
		openErrorDialog: function()
		{
			this._errorDialog = new BX.PopupWindow(
				this._id + "_error",
				null,
				{
					autoHide: true,
					draggable: true,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: { top: "10px", right: "15px" },
					zIndex: 0,
					titleBar: this.getMessage("dialogTitle"),
					content: this.getMessage("dialogSummary"),
					className : "crm-text-popup",
					lightShadow : true,
					buttons:
					[
						new BX.PopupWindowButton(
							{
								text : BX.message("JS_CORE_WINDOW_CONTINUE"),
								className : "popup-window-button-accept",
								events: { click: BX.delegate(this.closeErrorDialog, this) }
							}
						)
					]
				}
			);
			this._errorDialog.show();
		},
		closeErrorDialog: function()
		{
			if(this._errorDialog)
			{
				this._errorDialog.close();
				this._errorDialog.destroy();
				this._errorDialog = null;
			}
		},
		openSelector: function()
		{
			if(!this._selector)
			{
				this._selector = BX.CrmDealCategorySelectDialog.create(
					this._id,
					{
						value: -1,
						categoryIds: BX.prop.getArray(this._settings, "categoryIds", [])
					}
				);
				this._selector.addCloseListener(this._selectListener);
			}
			this._selector.open();
		},
		onSelect: function(sender, args)
		{
			if(!(BX.type.isBoolean(args["isCanceled"]) && args["isCanceled"] === false))
			{
				return;
			}

			BX.ajax(
				{
					url: this._serviceUrl,
					method: "POST",
					dataType: "json",
					data:
						{
							"ACTION": BX.prop.getString(this._settings, "action", "MOVE_TO_CATEGORY"),
							"ACTION_ENTITY_ID": this._entityId,
							"CATEGORY_ID": sender.getValue()
						},
					onsuccess: BX.delegate(this.onSuccess, this)
				}
			);
		},
		onSuccess: function(data)
		{
			var error = BX.prop.getString(data, "ERROR", "");
			if(error !== "")
			{
				this.openErrorDialog(error);
				return;
			}
			window.location.reload();
		}
	};

	if(typeof(BX.Crm.DealCategoryChanger.messages) === "undefined")
	{
		BX.Crm.DealCategoryChanger.messages = {};
	}

	BX.Crm.DealCategoryChanger.items = {};
	BX.Crm.DealCategoryChanger.create = function(id, settings)
	{
		var self = new BX.Crm.DealCategoryChanger();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}
