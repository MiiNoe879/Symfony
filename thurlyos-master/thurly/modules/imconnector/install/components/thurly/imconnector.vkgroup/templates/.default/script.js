;(function(window){
	window.showhideulvk = function(event)
	{
		var tableheightvk = document.getElementById('imconnector-vkgroup-li-table').offsetHeight;
		var liheightvk = tableheightvk + 31 +'px';

		document.getElementById('imconnector-vkgroup-li-hidden').style.height = liheightvk;
		document.getElementById('imconnector-vkgroup-li-hidden').style.padding = "15px 0";
		document.getElementById('imconnector-vkgroup-li-hidden').style.borderTop = "1px solid #eef2f5";
		document.getElementById('imconnector-vkgroup-li-show').style.height = "0";
		document.getElementById('imconnector-vkgroup-li-show').style.border = "none";
		return false;
	}
})(window);