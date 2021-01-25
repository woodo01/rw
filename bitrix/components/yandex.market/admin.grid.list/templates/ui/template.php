<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

use Bitrix\Main;

/** @var Yandex\Market\Components\AdminGridList $component */

$APPLICATION->SetAdditionalCSS('/bitrix/css/yandex.market/admin.css');

$adminList = $component->getViewList();

if (!($adminList instanceof CAdminUiList))
{
	ShowError('ui template only for CAdminUiList');
	return;
}

include __DIR__ . '/partials/prolog.php';

$adminList->CheckListMode();

if ($arParams['USE_FILTER'] && !empty($arResult['FILTER']))
{
	$adminList->DisplayFilter($arResult['FILTER']);
}

$listParameters = [];

if (empty($adminList->arActions))
{
	$listParameters['ACTION_PANEL'] = false;
}

$adminList->DisplayList($listParameters);
