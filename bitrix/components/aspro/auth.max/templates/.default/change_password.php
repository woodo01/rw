<?if( !defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true ) die();?>
<?$APPLICATION->AddChainItem("����� ������");?>
<?$APPLICATION->SetTitle("����� ������");?>
<?global $USER, $APPLICATION;
if( !$USER->IsAuthorized() ){?>
	<?$APPLICATION->IncludeComponent(
		"bitrix:system.auth.changepasswd",
		"main",array(
			"AUTH_URL" => $arParams["SEF_FOLDER"],
			"URL" => $arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["change"],
		),
		false
	);?>
<?}else{
	LocalRedirect( $arParams["PERSONAL"] );
}?>