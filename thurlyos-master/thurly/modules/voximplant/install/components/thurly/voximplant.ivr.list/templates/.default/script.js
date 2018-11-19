(function(window)
{
	BX.namespace('BX.VoxImplant');

	var instance = null;
	var ajaxUrl = "/thurly/components/thurly/voximplant.ivr.list/ajax.php";
	var gridId = "voximplant_ivr_list";

	BX.VoxImplant.IvrList = function()
	{

	};

	BX.VoxImplant.IvrList.getInstance = function()
	{
		if (instance === null)
		{
			instance = new BX.VoxImplant.IvrList();
		}

		return instance;
	};

	BX.VoxImplant.IvrList.prototype.delete = function(ivrId)
	{
		ivrId = parseInt(ivrId);
		var postParams = {
			action: 'delete',
			sessid: BX.thurly_sessid(),
			id: ivrId
		};
		var wait = BX.showWait();

		BX.ajax({
			url: ajaxUrl,
			method: 'POST',
			data: postParams,
			dataType: 'json',
			onsuccess: function(response)
			{
				BX.closeWait(null, wait);
				if(!response.SUCCESS)
				{
					var error = response.ERROR || 'Unknown error';
					window.alert(error);
				}
				else
				{
					var grid = BX.Main.gridManager.getInstanceById(gridId);
					if(grid)
						grid.reload();
				}
			},
			onfailure: function()
			{
				BX.closeWait(null, wait);
				window.alert("Network error");
			}
		})
	};
})(window);