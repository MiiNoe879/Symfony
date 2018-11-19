BX.namespace("BX.Voximplant");

BX.Voximplant.CRM = {
	init: function(ajaxUrl)
	{
		var button = BX('interface_crm_option_btn');
		BX.bind(button, 'click', function() {
			var node = BX.create('SPAN', {props : {className : "wait"}});
			BX.addClass(button, "webform-small-button-wait webform-small-button-active");
			this.appendChild(node);
			BX.ajax({
				method: 'POST',
				url: ajaxUrl,
				data: {'VI_SET_WORKFLOW_EXECUTION': 'Y', EXECUTION_PARAMETER : BX('interface_crm_option_form').elements.leadWorkflowAction.value, sessid : BX.thurly_sessid()},
				dataType: 'json',
				onsuccess: function()
				{
					BX.removeClass(button, "webform-small-button-wait webform-small-button-active");
					BX.remove(node);
				},
				onfailure: function()
				{
					BX.removeClass(button, "webform-small-button-wait webform-small-button-active");
					BX.remove(node);
				}
			});
			return false;
		});
	}
};