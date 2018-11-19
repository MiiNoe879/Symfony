<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var CThurlyComponentTemplate $this
 * @var \Thurly\Main\HttpRequest $request
 */
$request = \Thurly\Main\Context::getCurrent()->getRequest();

if (false && class_exists('Thurly\Tasks\Ui\Filter\Task'))
{
	if ($arParams["GROUP_ID"] > 0)
	{
		$isUser = false;
		\Thurly\Tasks\Ui\Filter\Task::setGroupId($arParams["GROUP_ID"]);
	}
	else
	{
		$isUser = true;
		\Thurly\Tasks\Ui\Filter\Task::setUserId($arParams["USER_ID"]);
	}

	$state = \Thurly\Tasks\Ui\Filter\Task::listStateInit()->getState();

	$this->__component->arResult = $APPLICATION->IncludeComponent(
		'thurly:tasks.task.list',
		'.default',
		$arParams + array(
			"FORCE_LIST_MODE" => "Y",
			"ITEMS_COUNT" => "50",
		) + ($isUser ? array(
			"PERSONAL" => "N",
			"STATE" => array(
				'ROLES'=>$state['ROLES'],
				'SELECTED_ROLES'=>$state['ROLES'],
				'VIEWS'=>$state['VIEWS'],
				'SELECTED_VIEWS'=>$state['VIEWS'],
			)
		) : array()),
		$this->__component
	);
}
else
{
	if ($request->getPost("search"))
	{
		$post = array("search" => $request->getPost("search"));
		CUtil::decodeURIComponent($post);
		$_GET["F_SEARCH_ALT"] = $post["search"];
	}
	$this->__component->arResult = $APPLICATION->IncludeComponent(
		'thurly:tasks.list',
		'.default',
		$arParams + array("FORCE_LIST_MODE" => "Y"),
		$this->__component
	);
}
