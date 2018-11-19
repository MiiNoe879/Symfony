<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
CUtil::InitJSCore(array("popup", "ajax"));

$this->addExternalCss(SITE_TEMPLATE_PATH."/css/profile_menu.css");
$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."profile-menu-mode");

$this->SetViewTarget("above_pagetitle", 100);

?>
<div class="profile-menu profile-menu-group">
	<div class="profile-menu-inner">
		<a
			href="<?=$arResult["Urls"]["View"]?>"
			class="profile-menu-avatar group-default-avatar"
			<?if (strlen($arResult["Group"]["IMAGE_FILE"]["src"]) > 0):?>
				style="background:url('<?=$arResult["Group"]["IMAGE_FILE"]["src"]?>') no-repeat center center; background-size: cover"
			<?endif;?>
		></a>
		<div class="profile-menu-right">
			<div class="profile-menu-info<?=($arResult["Group"]["IS_EXTRANET"] == "Y" ? " profile-menu-group-info-extranet" : "")?>">
				<a href="<?=$arResult["Urls"]["View"]?>" class="profile-menu-name"><?=$arResult["Group"]["NAME"]?></a>
				<?if($arResult["Group"]["CLOSED"] == "Y"):?>
					<span class="profile-menu-description"><?=GetMessage("SONET_UM_ARCHIVE_GROUP")?></span>
				<?endif?>
			</div>
			<div class="profile-menu-items-new"><?

				$menuItems = array();

				foreach ($arResult["CanView"] as $key => $val)
				{
					if (
						!$val
						|| $key == "content_search")
					{
						continue;
					}

					if ($key == 'general')
					{
						$menuItems[] = array(
							"TEXT" => GetMessage("SONET_UM_GENERAL"),
							"URL" => ($arResult["Urls"]["General"] ? $arResult["Urls"]["General"] : $arResult["Urls"]["View"]),
							"ID" => "general",
							"IS_ACTIVE" => in_array($arParams["PAGE_ID"], array("group", "group_general")),
						);
					}
					else
					{
						$item = array(
							"TEXT" => $arResult["Title"][$key],
							"ID" => $key,
							"IS_ACTIVE" => ($arParams["PAGE_ID"] == "group_".$key),
						);

						if (
							!empty($arResult["OnClicks"])
							&& !empty($arResult["OnClicks"][$key])
						)
						{
							$item["ON_CLICK"] = $arResult["OnClicks"][$key];
						}
						else
						{
							$item["URL"] = $arResult["Urls"][$key];
						}

						$menuItems[] = $item;
					}
				}

				$APPLICATION->IncludeComponent(
					"thurly:main.interface.buttons",
					"",
					array(
						"ID" => $arResult["menuId"],
						"ITEMS" => $menuItems,
					)
				);

			?></div>
		</div>
	</div>
</div>
<?


$this->EndViewTarget();?>