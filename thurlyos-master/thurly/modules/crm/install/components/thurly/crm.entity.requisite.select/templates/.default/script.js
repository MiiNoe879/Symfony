BX.namespace("BX.Crm");

if(typeof(BX.Crm.EntityRequisiteSelector) === "undefined")
{
	BX.Crm.EntityRequisiteSelector = function()
	{
		this._id = "";
		this._settings = {};
		this._editorSaveHandler = BX.delegate(this.onEditorSave, this);
		this._editorCancelHandler = BX.delegate(this.onEditorCancel, this);
		this._sliderCloseHandler = BX.delegate(this.onSliderClose, this);
	};
	BX.Crm.EntityRequisiteSelector.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this.bindEvents();

			//Prevent caching of requisite selector slider
			if(typeof(top.BX.ThurlyOS.Slider) !== "undefined")
			{
				var sliderPage = top.BX.ThurlyOS.Slider.getCurrentPage();
				if(sliderPage)
				{
					BX.addCustomEvent(
						sliderPage.getWindow(),
						"BX.ThurlyOS.PageSlider:onClose",
						this._sliderCloseHandler
					);
				}
			}
		},
		bindEvents: function()
		{
			BX.addCustomEvent(window, "BX.Crm.EntityEditor:onSave", this._editorSaveHandler);
			BX.addCustomEvent(window, "BX.Crm.EntityEditor:onCancel", this._editorCancelHandler);
		},
		unbindEvents: function()
		{
			BX.removeCustomEvent(window, "BX.Crm.EntityEditor:onSave", this._editorSaveHandler);
			BX.removeCustomEvent(window, "BX.Crm.EntityEditor:onCancel", this._editorCancelHandler);
		},
		onEditorSave: function(sender, eventArags)
		{
			if(this._id !== BX.prop.getString(eventArags, "id"))
			{
				return;
			}

			BX.localStorage.set(
				"BX.Crm.EntityRequisiteSelector:onSave",
				{
					context: eventArags["externalContext"],
					entityTypeId: eventArags["entityTypeId"],
					entityId: eventArags["entityId"],
					requisiteId: eventArags["model"].getField("REQUISITE_ID", 0),
					bankDetailId: eventArags["model"].getField("BANK_DETAIL_ID", 0)
				},
				10
			);

			eventArags["cancel"] = true;
			this.unbindEvents();
			this.closeSlider();
		},
		onEditorCancel: function(sender, eventArags)
		{
			if(this._id !== BX.prop.getString(eventArags, "id"))
			{
				return;
			}

			BX.localStorage.set(
				"BX.Crm.EntityRequisiteSelector:onCancel",
				{
					context: eventArags["externalContext"],
					entityTypeId: eventArags["entityTypeId"],
					entityId: eventArags["entityId"],
					requisiteId: eventArags["model"].getField("REQUISITE_ID", 0),
					bankDetailId: eventArags["model"].getField("BANK_DETAIL_ID", 0)
				},
				10
			);

			eventArags["cancel"] = true;
			this.unbindEvents();
			this.closeSlider();
		},
		closeSlider: function()
		{
			if(typeof(top.BX.ThurlyOS.Slider) !== "undefined")
			{
				setTimeout(
					function(){ top.BX.ThurlyOS.Slider.close(false) },
					250
				);
			}
		},
		onSliderClose: function(slider)
		{
			setTimeout(
				function(){ top.BX.ThurlyOS.Slider.destroy(slider.getUrl()) },
				1000
			);
		}
	};
	BX.Crm.EntityRequisiteSelector.create = function(id, settings)
	{
		var self = new BX.Crm.EntityRequisiteSelector();
		self.initialize(id, settings);
		return self;
	};
}