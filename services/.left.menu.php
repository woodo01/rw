<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("SERVICES_MENU_MEETING_ROOM"),
		"/services/index.php",
		Array("/services/res_c.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')"
	),
	Array(
		GetMessage("SERVICES_MENU_LISTS"),
		"/services/lists/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Lists')"
	),
	Array(
		GetMessage("SERVICES_MENU_REQUESTS"),
		"/services/requests/",
		Array(),
		Array(),
		(!IsModuleInstalled("form"))?"false":"CBXFeatures::IsFeatureEnabled('Requests')"
	),
	Array(
		GetMessage("SERVICES_MENU_LEARNING"),
		"/services/learning/",
		Array("/services/course.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Learning')"
	),
	Array(
		GetMessage("SERVICES_MENU_WIKI"),
		"/services/wiki/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Wiki')"
	),
	Array(
		GetMessage("SERVICES_MENU_CONTACT_CENTER"),
		"/services/contact_center/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("SERVICES_MENU_FAQ"),
		"/services/faq/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("SERVICES_MENU_SUBSCR"),
		"/services/subscr_edit.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Subscribe')"
	),
	Array(
		GetMessage("SERVICES_MENU_EVENTLIST"),
		"/services/event_list.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('EventList')"
	),
	Array(
		GetMessage("SERVICES_MENU_SALARY"),
		"/services/salary/",
		Array(),
		Array(),
		"LANGUAGE_ID == 'ru' && CBXFeatures::IsFeatureEnabled('Salary')"
	),
	Array(
		GetMessage("SERVICES_MENU_TELEPHONY"),
		"/services/telephony/",
		Array(),
		Array(),
		'CModule::IncludeModule("voximplant") && SITE_TEMPLATE_ID !== "bitrix24" && Bitrix\Voximplant\Security\Helper::isMainMenuEnabled()'
	),
	/*Array(
		GetMessage("SERVICES_MENU_OPENLINES"),
		"/services/openlines/",
		Array(),
		Array(),
		'CModule::IncludeModule("imopenlines") && SITE_TEMPLATE_ID !== "bitrix24" && Bitrix\ImOpenlines\Security\Helper::isMainMenuEnabled()'
	),*/
);
?>