if(typeof(BX.InterfaceToolBar) === "undefined")
{
	BX.InterfaceToolBar = function()
	{
		this._id = "";
		this._settings = null;
		this._container = null;
		this._menuButton = null;
		this._menuPopup = null;
		this._isMenuOpened = false;
	};

	BX.InterfaceToolBar.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : BX.CrmParamBag.create(null);
			this._menuButton = this._container = BX(this.getSetting("buttonId", ""));
			if(this._menuButton)
			{
				BX.bind(this._menuButton, 'click', BX.delegate(this.onMenuButtonClick, this));
			}
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.getParam(name, defaultval);
		},
		openMenu: function(e)
		{
			if(this._isMenuOpened)
			{
				this.closeMenu();
				return;
			}

			var items = this.getSetting('items', null);
			if(!BX.type.isArray(items))
			{
				return;
			}

			var hdlrRx1 = /return\s+false(\s*;)?\s*$/;
			var hdlrRx2 = /;\s*$/;
			var menuItems = [];
			for(var i = 0; i < items.length; i++)
			{
				var item = items[i];

				var isSeparator = typeof(item["SEPARATOR"]) !== "undefined" ? item["SEPARATOR"] : false;
				if(isSeparator)
				{
					menuItems.push({ "SEPARATOR": true, delimiter: true });
					continue;
				}

				var link = typeof(item["LINK"]) !== "undefined" ? item["LINK"] : "";
				var hdlr = typeof(item["ONCLICK"]) !== "undefined" ? item["ONCLICK"] : "";

				if(link !== "")
				{
					var s = "window.location.href = \"" + link + "\";";
					hdlr = hdlr !== "" ? (s + " " + hdlr) : s;
				}

				if(hdlr !== "")
				{
					if(!hdlrRx1.test(hdlr))
					{
						if(!hdlrRx2.test(hdlr))
						{
							hdlr += ";";
						}
						hdlr += " return false;";
					}
				}

				menuItems.push(
					{
						text:  typeof(item["TEXT"]) !== "undefined" ? item["TEXT"] : "",
						onclick: hdlr
					}
				);
			}

			this._menuId = this._id.toLowerCase() + "_menu";

			BX.PopupMenu.show(
				this._menuId,
				this._menuButton,
				menuItems,
				{
					"offsetTop": 0,
					"offsetLeft": 0,
					"events":
						{
							"onPopupShow": BX.delegate(this.onPopupShow, this),
							"onPopupClose": BX.delegate(this.onPopupClose, this),
							"onPopupDestroy": BX.delegate(this.onPopupDestroy, this)
						}
				}
			);
			this._menuPopup = BX.PopupMenu.currentItem;
		},
		closeMenu: function()
		{
			if(this._menuPopup)
			{
				if(this._menuPopup.popupWindow)
				{
					this._menuPopup.popupWindow.destroy();
				}
			}
		},
		onMenuButtonClick: function(e)
		{
			this.openMenu();
		},
		onPopupShow: function()
		{
			this._isMenuOpened = true;
		},
		onPopupClose: function()
		{
			this.closeMenu();
		},
		onPopupDestroy: function()
		{
			this._isMenuOpened = false;
			this._menuPopup = null;

			if(typeof(BX.PopupMenu.Data[this._menuId]) !== "undefined")
			{
				delete(BX.PopupMenu.Data[this._menuId]);
			}
		}
	};

	BX.InterfaceToolBar.create = function(id, settings)
	{
		var self = new BX.InterfaceToolBar();
		self.initialize(id, settings);
		return self;
	};
}
