(function() {

	//"use strict";

	BX.namespace("BX.CRM.Kanban");

	/**
	 *
	 * @param options
	 * @extends {BX.Kanban.Column}
	 * @constructor
	 */
	BX.CRM.Kanban.Column = function(options)
	{
		BX.Kanban.Column.apply(this, arguments);
	};

	BX.CRM.Kanban.Column.prototype = {
		__proto__: BX.Kanban.Column.prototype,
		constructor: BX.CRM.Kanban.Column,
		renderSubtitleTime: 6,
		pathToAdd: null,

		/**
		 * Custom format method from BX.Currency.
		 * @param {float} price Price.
		 * @param {string} currency Currency.
		 * @param {boolean} useTemplate Use or not template.
		 * @returns {string}
		 */
		currencyFormat: function (price, currency, useTemplate)
		{
			var result = "",
				format;

			if (typeof BX.Currency === "undefined")
			{
				return price;
			}

			useTemplate = !!useTemplate;
			format = BX.Currency.getCurrencyFormat(currency);

			if (!!format && typeof format === "object")
			{
				format.CURRENT_DECIMALS = format.DECIMALS;
				format.HIDE_ZERO = "Y";//always
				if (format.HIDE_ZERO === "Y" && price == parseInt(price, 10))
				{
					format.CURRENT_DECIMALS = 0;
				}

				result = BX.util.number_format(
					price,
					format.CURRENT_DECIMALS,
					format.DEC_POINT,
					format.THOUSANDS_SEP
				);
				if (useTemplate)
				{
					result = format.FORMAT_STRING.replace(/(^|[^&])#/, "$1" + result);
				}
			}
			return result;
		},

		/**
		 * Decrement total price of column.
		 * @param {Number} val Value to decrement.
		 * @returns {void}
		 */
		decPrice: function(val)
		{
			var data = this.getData();
			data.sum = parseFloat(data.sum) - val;
			this.setData(data);
		},

		/**
		 * Increment total price of column.
		 * @param {Integer} val Value to increment.
		 * @returns {void}
		 */
		incPrice: function(val)
		{
			var data = this.getData();
			data.sum = parseFloat(data.sum) + val;
			this.setData(data);
		},

		/**
		 * Return add-button for new column.
		 * @returns {DOM|null}
		 */
		getAddColumnButton: function ()
		{
			var columnData = this.getData();

			if (columnData.type === "WIN")
			{
				this.layout.info.style.marginRight = "0";
				return BX.create("div");
			}
			else
			{
				return BX.Kanban.Column.prototype.getAddColumnButton.apply(this, arguments);
			}
		},

		/**
		 * Get path for add mew element.
		 * @returns {string}
		 */
		getAddPath: function()
		{
			if (this.pathToAdd !== null)
			{
				return this.pathToAdd;
			}

			var gridData = this.getGridData();
			var type = gridData.entityType.toLowerCase();
			var wrapperId, button;

			if (type === "invoice")
			{
				wrapperId = "crm_invoice_toolbar";
			}
			else
			{
				wrapperId = "toolbar_" + type + "_list";
			}

			button = BX(wrapperId).querySelector("a");
			this.pathToAdd = button.getAttribute("href");
			
			this.pathToAdd += this.pathToAdd.indexOf("?") === -1 ? "?" : "&";
			
			return this.pathToAdd;
		},

		/**
		 * Renders subtitle content.
		 * @returns {Element}
		 */
		renderSubTitle: function()
		{
			var data = this.getData();
			var gridData = this.getGridData();
			
			// render layout first time

			if (gridData.entityType !== "LEAD")
			{
				this.layout.subTitlePriceText = BX.create("span", {
					attrs: {
						className: "crm-kanban-total-price-total"
					}
				});
				this.layout.subTitlePrice = BX.create("div", {
					attrs: {
						className: "crm-kanban-total-price"
					},
					children: [
						this.layout.subTitlePriceText
					]
				});
			}
			else
			{
				this.layout.subTitlePrice = null;
			}

			// animate change
			if (this.layout.subTitlePriceText)
			{
				data.sum = parseFloat(data.sum);
				data.sum_old = data.sum_old ? data.sum_old : data.sum_init;
				data.sum_init = data.sum;

				this.renderSubTitleAnimation(
					data.sum_old,
					data.sum,
					Math.abs(data.sum_old - data.sum) / 20,
					this.layout.subTitlePriceText,
					function (element, value)
					{
						element.innerHTML = this.currencyFormat(
							Math.round(value),
							gridData.currency,
							true
						);
						data.sum_old = data.sum;
					}.bind(this)
				);

				this.setData(data);
			}
			
			return BX.create("div", {
				children: [
					this.layout.subTitlePrice,
					BX.create("a", {
						attrs: {
							className: "crm-kanban-column-add-item-button",
							href: this.getAddPath() +
							(
								gridData.entityType === "DEAL"
									? "stage_id="
									: "status_id="
							) +
							this.getId()
						},
						text: "+"
					})
				]
			});
		},

		/**
		 * Animate change from start to val with step in element.
		 * @param {Number} start
		 * @param {Number} value
		 * @param {Number} step
		 * @param {DOM} element
		 * @param {Function} finalCall Call finaly for element with val.
		 * @returns {void}
		 */
		renderSubTitleAnimation: function(start, value, step, element, finalCall)
		{
			var i = +start;
			var val = parseFloat(value);
			var timeout = this.renderSubtitleTime;

			if (i < val)
			{
				(function ()
				{
					if (i <= val)
					{
						setTimeout(arguments.callee, timeout);
						element.textContent = BX.util.number_format(i, 0, ",", " ");
						i = i + step;
					}
					else
					{
						if (typeof finalCall === "function")
						{
							finalCall(element, value);
						}
					}
				})();
			}
			else if (i > val)
			{
				(function ()
				{
					if (i >= val)
					{
						setTimeout(arguments.callee, timeout);
						element.textContent = BX.util.number_format(i, 0, ",", " ");
						i = i - step;
					}
					else
					{
						if (typeof finalCall === "function")
						{
							finalCall(element, value);
						}
					}
				})();
			}
			else if (typeof finalCall === "function")
			{
				finalCall(element, value);
			}
		},

		/**
		 * Hook on add column button.
		 * @param {MouseEvent} event
		 * @returns {void}
		 */
		handleAddColumnButtonClick: function(event)
		{
			var gridData = this.getGridData();
			// if no access, show access-query popup
			if (
				gridData.rights &&
				gridData.rights.canAddColumn
			)
			{
				BX.Kanban.Column.prototype.handleAddColumnButtonClick.apply(this, arguments);
			}
			else if (typeof BX.Intranet !== "undefined")
			{
				this.getGrid().accessNotify();
			}
		},

		/**
		 * Switch from view to edit mode (column).
		 * @returns {void}
		 */
		switchToEditMode: function()
		{
			var gridData = this.getGridData();
			// if no access, show access-query popup
			if (
				gridData.rights &&
				gridData.rights.canAddColumn
			)
			{
				BX.Kanban.Column.prototype.switchToEditMode.apply(this, arguments);
			}
			else if (typeof BX.Intranet !== "undefined")
			{
				this.getGrid().accessNotify();
			}
		}
	};

})();