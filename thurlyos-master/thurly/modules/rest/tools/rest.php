<?php
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/prolog_before.php");

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$result = array();
$request = Thurly\Main\Context::getCurrent()->getRequest();

if($request->isPost() && check_thurly_sessid() && \Thurly\Main\Loader::includeModule('rest'))
{
	$action = $request["action"];
	$admin = \CRestUtil::isAdmin();

	switch($action)
	{
		case 'install':

			if($admin)
			{
				if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
				{
					try
					{
						\Thurly\Rest\OAuthService::register();
					}
					catch(\Thurly\Main\SystemException $e)
					{
						$result = array('error' => $e->getCode(), 'error_description' => $e->getMessage());
					}
				}

				if(\Thurly\Rest\OAuthService::getEngine()->isRegistered())
				{
					$code = $request["code"];
					$ver = isset($request['version']) ? $request['version'] : false;

					$result = array("error" => Loc::getMessage('RMP_INSTALL_ERROR'));

					$appDetailInfo = false;
					if(strlen($code) > 0)
					{
						if(isset($request["check_hash"]) && isset($request["install_hash"]))
						{
							$appDetailInfo = \Thurly\Rest\Marketplace\Client::getInstall($code, $ver, $request["check_hash"], $request["install_hash"]);
						}
						else
						{
							$appDetailInfo = \Thurly\Rest\Marketplace\Client::getInstall($code, $ver);
						}

						if($appDetailInfo)
						{
							$appDetailInfo = $appDetailInfo['ITEMS'];
						}
					}

					if($appDetailInfo)
					{
						$queryFields = array(
							'CLIENT_ID' => $appDetailInfo['APP_CODE'],
							'VERSION' => $appDetailInfo['VER'],
						);

						if(isset($request["check_hash"]) && isset($request["install_hash"]))
						{
							$queryFields["CHECK_HASH"] = $request["check_hash"];
							$queryFields["INSTALL_HASH"] = $request["install_hash"];
						}

						$installResult = \Thurly\Rest\OAuthService::getEngine()
								->getClient()
								->installApplication($queryFields);

						if($installResult['error'])
						{
							$result['error_description'] = $installResult['error'].': '.$installResult['error_description'];
						}
						elseif($installResult['result'])
						{
							$appFields = array(
								'CLIENT_ID' => $installResult['result']['client_id'],
								'CODE' => $appDetailInfo['CODE'],
								'ACTIVE' => \Thurly\Rest\AppTable::ACTIVE,
								'INSTALLED' => ($appDetailInfo["OPEN_API"] === "Y" || empty($appDetailInfo['INSTALL_URL']))
									? \Thurly\Rest\AppTable::INSTALLED
									: \Thurly\Rest\AppTable::NOT_INSTALLED,
								'URL' => $appDetailInfo['URL'],
								'URL_DEMO' => $appDetailInfo['DEMO_URL'],
								'URL_INSTALL' => $appDetailInfo['INSTALL_URL'],
								'VERSION' => $installResult['result']['version'],
								'SCOPE' => implode(',', $installResult['result']['scope']),
								'STATUS' => $installResult['result']['status'],
								'SHARED_KEY' => $appDetailInfo['SHARED_KEY'],
								'CLIENT_SECRET' => '',
								'APP_NAME' => $appDetailInfo['NAME'],
								'MOBILE' => $appDetailInfo['BXMOBILE'] == 'Y' ? \Thurly\Rest\AppTable::ACTIVE : \Thurly\Rest\AppTable::INACTIVE,
							);

							if(
								$appFields['STATUS'] === \Thurly\Rest\AppTable::STATUS_TRIAL
								|| $appFields['STATUS'] === \Thurly\Rest\AppTable::STATUS_PAID
							)
							{
								$appFields['DATE_FINISH'] = \Thurly\Main\Type\DateTime::createFromTimestamp($installResult['result']['date_finish']);
							}
							else
							{
								$appFields['DATE_FINISH'] = '';
							}

							$existingApp = \Thurly\Rest\AppTable::getByClientId($appFields['CLIENT_ID']);

							if($existingApp)
							{
								$addResult = \Thurly\Rest\AppTable::update($existingApp['ID'], $appFields);
							}
							else
							{
								$addResult = \Thurly\Rest\AppTable::add($appFields);
							}

							if($addResult->isSuccess())
							{
								$appId = $addResult->getId();

								if(is_array($appDetailInfo['MENU_TITLE']))
								{
									foreach($appDetailInfo['MENU_TITLE'] as $lang => $langName)
									{
										$appLangFields = array(
											'APP_ID' => $appId,
											'LANGUAGE_ID' => $lang,
											'MENU_NAME' => $langName
										);

										$appLangUpdateFields = array(
											'MENU_NAME' => $langName
										);

										$connection = \Thurly\Main\Application::getConnection();
										$queries = $connection->getSqlHelper()->prepareMerge(
											\Thurly\Rest\AppLangTable::getTableName(),
											array('APP_ID', 'LANGUAGE_ID'),
											$appLangFields,
											$appLangUpdateFields
										);

										foreach($queries as $query)
										{
											$connection->queryExecute($query);
										}
									}
								}

								if($appDetailInfo["OPEN_API"] === "Y" && !empty($appFields["URL_INSTALL"]))
								{
									// checkCallback is already called inside checkFields
									$result = \Thurly\Rest\EventTable::add(array(
										"APP_ID" => $appId,
										"EVENT_NAME" => "ONAPPINSTALL",
										"EVENT_HANDLER" => $appFields["URL_INSTALL"],
									));
									if($result->isSuccess())
									{
										\Thurly\Rest\Event\Sender::bind('rest', 'OnRestAppInstall');
									}
								}

								\Thurly\Rest\AppTable::install($appId);

								$result = array(
									'success' => 1,
									'id' => $appId,
									'open' => $appDetailInfo["OPEN_API"] !== "Y",
									'installed' => $appFields['INSTALLED'] === 'Y',
									'redirect' => \CRestUtil::getApplicationPage($appId),
								);
							}
							else
							{
								$result['error_description'] = implode('<br />', $addResult->getErrorMessages());
							}
						}
					}
					else
					{
						$result = array('error' => Loc::getMessage('RMP_NOT_FOUND'));
					}
				}
				elseif(!$result['error'])
				{
					$result = array('error' => Loc::getMessage('RMP_INSTALL_ERROR'));
				}
			}
			else
			{
				$result = array('error' => Loc::getMessage('RMP_ACCESS_DENIED'));
			}

		break;

		case 'uninstall':

			if($admin)
			{
				$code = $request["code"];
				$clean = $request["clean"];

				$dbRes = \Thurly\Rest\AppTable::getList(array(
					'filter' => array(
						"=CODE" => $code,
						"!=STATUS" => \Thurly\Rest\AppTable::STATUS_LOCAL,
					),
				));

				$appInfo = $dbRes->fetch();
				if($appInfo)
				{
					\Thurly\Rest\AppTable::uninstall($appInfo['ID'], $clean == "true");

					$appFields = array(
						'ACTIVE' => 'N',
						'INSTALLED' => 'N',
					);

					\Thurly\Rest\AppTable::update($appInfo['ID'], $appFields);

					$result = array('success' => 1);
				}
				else
				{
					$result = array('error' => Loc::getMessage('RMP_NOT_FOUND'));
				}
			}
			else
			{
				$result = array('error' => Loc::getMessage('RMP_ACCESS_DENIED'));
			}


		break;

		case 'reinstall':
			if($admin)
			{
				$ID = $request["id"];
				$appInfo = \Thurly\Rest\AppTable::getByClientId($ID);
				if($appInfo && $appInfo['STATUS'] === \Thurly\Rest\AppTable::STATUS_LOCAL)
				{
					if(empty($appInfo["MENU_NAME"]) && empty($appInfo["MENU_NAME_DEFAULT"]))
					{
						\Thurly\Rest\AppTable::install($appInfo['ID']);
						$result = array('success' => 1);
					}
					elseif(!empty($appInfo['URL_INSTALL']))
					{
						$appFields = array(
							'INSTALLED' => 'N',
						);

						\Thurly\Rest\AppTable::update($appInfo['ID'], $appFields);

						$result = array(
							'success' => 1,
							'redirect' => \CRestUtil::getApplicationPage($appInfo['ID']),
						);
					}
				}
				else
				{
					$result = array('error' => Loc::getMessage('RMP_NOT_FOUND'));
				}
			}
			else
			{
				$result = array('error' => Loc::getMessage('RMP_ACCESS_DENIED'));
			}

		break;

		case 'get_app_rigths':

			if($admin)
			{
				$appId = intval($request['app_id']);

				if($appId > 0)
				{
					$result = \Thurly\Rest\AppTable::getAccess($appId);
				}
				else
				{
					$result = 0;
				}
			}
			else
			{
				$result = array('error' => Loc::getMessage('RMP_ACCESS_DENIED'));
			}

		break;

		case "set_app_rights":
			if($admin)
			{
				$appId = intval($request['app_id']);

				if($appId > 0)
				{
					\Thurly\Rest\AppTable::setAccess($appId, $_POST["rights"]);
					\Thurly\Rest\PlacementTable::clearHandlerCache();
					$result = array('success' => 1);
				}
			}
			else
			{
				$result = array('error' => Loc::getMessage('RMP_ACCESS_DENIED'));
			}

		break;

		default:
			$result = array('error' => 'Unknown action');
	}
}

Header('Content-Type: application/json');
echo \Thurly\Main\Web\Json::encode($result);

require_once($_SERVER['DOCUMENT_ROOT']."/thurly/modules/main/include/epilog_after.php");