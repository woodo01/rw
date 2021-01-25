<?
$arResult = CMax::getChilds($arResult);
global $arRegion, $arTheme;

if($arResult){
	$MENU_TYPE = $arTheme['MEGA_MENU_TYPE']['VALUE'];
	$bRightSide = $arTheme['SHOW_RIGHT_SIDE']['VALUE'] == 'Y';	
	$bManyItemsMenu = ($MENU_TYPE == '4');

	$bRightBanner = $bRightSide && $arTheme['SHOW_RIGHT_SIDE']['DEPENDENT_PARAMS']['RIGHT_CONTENT']['VALUE'] == 'BANNER';
	$bRightBrand = $bRightSide && $arTheme['SHOW_RIGHT_SIDE']['DEPENDENT_PARAMS']['RIGHT_CONTENT']['VALUE'] == 'BRANDS';

	if($bRightBanner) {
		$bannerIblockId = CMaxCache::$arIBlocks[SITE_ID]["aspro_max_adv"]["aspro_max_banners_inner"][0];
		$arBannerFilter = array('!PROPERTY_SHOW_MENU' => false, 'ACTIVE' => 'Y', 'IBLOCK_ID' => $bannerIblockId);
		$arBannerSelect = array('ID', 'PROPERTY_SHOW_MENU');
		$arBanners = CMaxCache::CIblockElement_GetList(array("SORT" => "ASC", "CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($bannerIblockId))), $arBannerFilter, $arBannerSelect);
	}

	if($bRightBrand) {
		$brandIblockId = CMaxCache::$arIBlocks[SITE_ID]["aspro_max_content"]["aspro_max_brands"][0];
		$arBrandFilter = array('PROPERTY_SHOW_TOP_MENU_VALUE' => 'Y', 'ACTIVE' => 'Y', 'IBLOCK_ID' => $brandIblockId);
		$arBrandSelect = array('ID', 'PROPERTY_SHOW_TOP_MENU', 'PREVIEW_PICTURE', 'NAME', 'DETAIL_PAGE_URL', 'IBLOCK_ID');
		$arResult['BRANDS'] = CMaxCache::CIblockElement_GetList(array("SORT" => "ASC", "CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($brandIblockId))), $arBrandFilter, false, false, $arBrandSelect);
	}

	if($MENU_TYPE == 3) {
		$linksIblockId = CMaxCache::$arIBlocks[SITE_ID]["aspro_max_catalog"]["aspro_max_megamenu"][0];
		$arLinkFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $linksIblockId);
		$arLinkSelect = array('ID', 'PROPERTY_URL', 'NAME', 'IBLOCK_SECTION_ID');
		$arLinks = CMaxCache::CIblockElement_GetList(array("SORT" => "ASC", "CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($linksIblockId))), $arLinkFilter, false, false, $arLinkSelect);

		$arLinkSectionFilter = array('ACTIVE' => 'Y', 'IBLOCK_ID' => $linksIblockId);
		$arLinkSectionSelect = array('ID', 'UF_MENU_LINK', 'UF_MEGA_MENU_LINK', 'UF_CATALOG_ICON', 'NAME', 'IBLOCK_SECTION_ID', 'PICTURE', 'DEPTH_LEVEL');
		$arLinkSections = CMaxCache::CIblockSection_GetList(array("SORT" => "ASC", "CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($linksIblockId))), $arLinkSectionFilter, false, $arLinkSectionSelect);

		foreach ($arLinks as $link) {
			if(isset($link['IBLOCK_SECTION_ID'])) {
				$arChildLinks[$link['IBLOCK_SECTION_ID']][] = array(
					'TEXT' => $link['NAME'],
					'LINK' => $link['PROPERTY_URL_VALUE'],
					'SELECTED' => ($APPLICATION->GetCurPage() == $link['PROPERTY_URL_VALUE']),
				);
			}
		}

		foreach ($arLinkSections as $linkSection) {
			if($linkSection['UF_MENU_LINK'] && $linkSection['DEPTH_LEVEL'] == 1) {
				$arMegaLinks[$linkSection['ID']] = array(
					'LINK' => $linkSection['UF_MENU_LINK'],
				);
			}

			if($linkSection['DEPTH_LEVEL'] > 1) {
				$arMegaLinks[$linkSection['IBLOCK_SECTION_ID']]['CHILD'][$linkSection['ID']] = array(
					'TEXT' => $linkSection['NAME'],
					'LINK' => $linkSection['UF_MEGA_MENU_LINK'],
					'PARAMS' => array(
						'PICTURE' => $linkSection['PICTURE'],
						'SECTION_ICON' => $linkSection['UF_CATALOG_ICON'],
					),
					'SELECTED' => ($APPLICATION->GetCurPage() == $linkSection['UF_MEGA_MENU_LINK']),
				);
				if($arChildLinks[$linkSection['ID']]) {
					$arMegaLinks[$linkSection['IBLOCK_SECTION_ID']]['CHILD'][$linkSection['ID']]['CHILD'] = $arChildLinks[$linkSection['ID']];
				}
			}
		}

	}

	foreach($arResult as $key=>$arItem)
	{
		$bWideMenu = (isset($arItem['PARAMS']['CLASS']) && strpos($arItem['PARAMS']['CLASS'], 'wide_menu') !== false);

		if($MENU_TYPE == 3){
			foreach ($arMegaLinks as $megaLink) {
				if($megaLink['LINK'] == $arItem['LINK']) {
					$arResult[$key]["CHILD"] = $megaLink['CHILD'];
				}
			}
		}

		if($arBanners && $bWideMenu) {
			foreach ($arBanners as $banner) {
				if(is_array($banner['PROPERTY_SHOW_MENU_VALUE'])) {
					foreach ($banner['PROPERTY_SHOW_MENU_VALUE'] as $link) {
						if($link == $arItem['LINK']) {
							$arResult[$key]["BANNERS"][] = $banner['ID'];
						}
					}
				} else {
					if($banner['PROPERTY_SHOW_MENU_VALUE'] == $arItem['LINK']) {
						$arResult[$key]["BANNERS"][] = $banner['ID'];
					}
				}
			}
		}

		if(isset($arItem['CHILD']))
		{
			foreach($arItem['CHILD'] as $key2=>$arItemChild)
			{
				if($bManyItemsMenu) {
					if($bRightBrand && $arItemChild['PARAMS']['BRANDS']) {
						$brandIblockId = CMaxCache::$arIBlocks[SITE_ID]["aspro_max_content"]["aspro_max_brands"][0];
						$arBrandFilter = array('ID' => $arItemChild['PARAMS']['BRANDS'], 'ACTIVE' => 'Y', 'IBLOCK_ID' => $brandIblockId);
						$arBrandSelect = array('ID', 'PREVIEW_PICTURE', 'NAME', 'DETAIL_PAGE_URL', 'IBLOCK_ID');
						$arResult[$key]['CHILD'][$key2]['BRANDS'] = CMaxCache::CIblockElement_GetList(array("SORT" => "ASC", "NAME" => "ASC", "CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($brandIblockId))), $arBrandFilter, false, false, $arBrandSelect);
					}
				}
			}
		}
	}
}

?>