;(function(window){

	window.showhideulfb = function(event)
	{
		var tableheightfb = document.getElementById('imconnector-facebook-li-table').offsetHeight;
		var liheightfb = tableheightfb + 31 +'px';

		document.getElementById('imconnector-facebook-li-hidden').style.height = liheightfb;
		document.getElementById('imconnector-facebook-li-hidden').style.padding = "15px 0";
		document.getElementById('imconnector-facebook-li-hidden').style.borderTop = "1px solid #eef2f5";
		document.getElementById('imconnector-facebook-li-show').style.height = "0";
		document.getElementById('imconnector-facebook-li-show').style.border = "none";
		return false;
	};
})(window);