(function(window){

	window.showhideulfbcomments = function(event)
	{
		var tableheightfbcomments = document.getElementById('imconnector-facebook-comments-li-table').offsetHeight;
		var liheightfbcomments = tableheightfbcomments + 31 +'px';

		document.getElementById('imconnector-facebook-comments-li-hidden').style.height = liheightfbcomments;
		document.getElementById('imconnector-facebook-comments-li-hidden').style.padding = "15px 0";
		document.getElementById('imconnector-facebook-comments-li-hidden').style.borderTop = "1px solid #eef2f5";
		document.getElementById('imconnector-facebook-comments-li-show').style.height = "0";
		document.getElementById('imconnector-facebook-comments-li-show').style.border = "none";
		return false;
	};
})(window);