<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_AGENT_CHECK", true);

if(isset($_REQUEST['tpl']) && isset($_REQUEST['tpls']))
{
	define('SITE_TEMPLATE_ID', $_REQUEST['tpl']);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

/**
 * Thurly vars
 *
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

$request = \Thurly\Main\Context::getCurrent()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter());

if(check_thurly_sessid())
{
	$fields = $request['FIELDS'];
	if(!is_array($fields))
	{
		$fields = array();
	}

	$userFieldDispatcher = \Thurly\Main\UserField\Dispatcher::instance();

	if(
		isset($_REQUEST['tpl'])
		&& isset($_REQUEST['tpls'])
		&& !$userFieldDispatcher->getSignatureManager()->validateSignature(SITE_TEMPLATE_ID, $request['tpls'])
	)
	{
		die();
	}

	if(isset($request['lang']))
	{
		$userFieldDispatcher->setLanguage($request['lang']);
	}

	foreach($fields as $fieldInfo)
	{
		if(isset($request['action']))
		{
			switch($request['action'])
			{
				case 'add':
					$userFieldDispatcher->createField($fieldInfo);

				break;

				case 'update':
					$userFieldDispatcher->editField($fieldInfo);

				break;

				case 'delete':
					$userFieldDispatcher->deleteField($fieldInfo);

				break;

				case 'validate':
					$userFieldDispatcher->validateField($fieldInfo);

				break;
			}
		}
		else
		{
			$userFieldDispatcher->addField($fieldInfo);
		}
	}

	$mode = $request['mode'];
	if($mode !== 'view')
	{
		$mode = 'edit';
	}

	$view = null;
	switch($mode)
	{
		case 'edit':

			$view = new \Thurly\Main\UserField\DisplayEdit();

			if(isset($request['FORM']))
			{
				$view->setAdditionalParameter('form_name', $request['FORM'], true);
			}

			break;

		case 'view':

			$view = new \Thurly\Main\UserField\DisplayView();

		break;
	}

	if(isset($request['CONTEXT']))
	{
		$view->setAdditionalParameter('CONTEXT', $request['CONTEXT'], true);
	}

	$userFieldDispatcher->setView($view);

	$result = $userFieldDispatcher->getResult();

	Header('Content-Type: application/json');
	echo \Thurly\Main\Web\Json::encode($result);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");