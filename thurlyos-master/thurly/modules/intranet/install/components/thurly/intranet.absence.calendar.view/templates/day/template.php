<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<script type="text/javascript">
if (window.JCCalendarViewDay)
	jsBXAC.SetViewHandler(new JCCalendarViewDay());
else
	BX.loadScript(
		'/thurly/components/thurly/intranet.absence.calendar.view/templates/day/view.js', 
		function() {jsBXAC.SetViewHandler(new JCCalendarViewDay())}
	);
</script>