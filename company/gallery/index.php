<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/gallery/index.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?>

<?$APPLICATION->IncludeComponent("bitrix:photogallery_user", ".default", Array(
	"SECTION_PAGE_ELEMENTS"	=>	"100",
	"ELEMENTS_PAGE_ELEMENTS"	=>	"100",
	"PAGE_NAVIGATION_TEMPLATE"	=>	"",
	"ELEMENTS_USE_DESC_PAGE"	=>	"Y",
	"IBLOCK_TYPE"	=>	"photos",
	"IBLOCK_ID"	=>	"14",
	"GALLERY_GROUPS"	=>	array(
		0	=>	"13",
		1	=>	"1"
	),
	"ONLY_ONE_GALLERY"	=>	"Y",
	"SECTION_SORT_BY"	=>	"ID",
	"SECTION_SORT_ORD"	=>	"ASC",
	"ELEMENT_SORT_FIELD"	=>	"id",
	"ELEMENT_SORT_ORDER"	=>	"desc",
	"ANALIZE_SOCNET_PERMISSION"	=>	"Y",
	"UPLOAD_MAX_FILE_SIZE"	=>	"64",
	"GALLERY_AVATAR_SIZE"	=>	"50",
	"ALBUM_PHOTO_THUMBS_SIZE"	=>	"150",
	"ALBUM_PHOTO_SIZE"	=>	"150",
	"THUMBS_SIZE"	=>	"250",
	"PREVIEW_SIZE"	=>	"700",
	"JPEG_QUALITY1"	=>	"95",
	"JPEG_QUALITY2"	=>	"95",
	"JPEG_QUALITY"	=>	"90",
	"WATERMARK_MIN_PICTURE_SIZE"	=>	"200",
	"ADDITIONAL_SIGHTS"	=>	array(),
	"UPLOAD_MAX_FILE"	=>	"1",
	"PATH_TO_FONT"	=>	"",
	"SEF_MODE"	=>	"Y",
	"SEF_FOLDER"	=>	"/company/gallery/",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"DATE_TIME_FORMAT_SECTION"	=>	"d.m.Y H:i:s",
	"DATE_TIME_FORMAT_DETAIL"	=>	"d.m.Y H:i:s",
	"DISPLAY_PANEL"	=>	"N",
	"SET_TITLE"	=>	"Y",
	"USE_RATING"	=>	"N",
	"DISPLAY_AS_RATING" => "rating_main",
	"MAX_VOTE"	=>	"5",
	"VOTE_NAMES"	=>	array(
		0	=>	"0",
		1	=>	"1",
		2	=>	"2",
		3	=>	"3",
		4	=>	"4",
	),
	"SHOW_TAGS"	=>	"N",
	"ORIGINAL_SIZE" =>	"1280",
	"UPLOADER_TYPE" =>	"form",
	"USE_COMMENTS"	=>	"Y",
	"COMMENTS_TYPE"	=>	"forum",
	"FORUM_ID"	=>	"2",
	"PATH_TO_SMILE"	=>	"/bitrix/images/blog/smile/",
	"URL_TEMPLATES_READ"	=>	"",
	"USE_CAPTCHA"	=>	"N",
	"SHOW_LINK_TO_FORUM"	=>	"N",
	"PREORDER"	=>	"Y",
	"MODERATE"	=>	"N",
	"SHOW_ONLY_PUBLIC"	=>	"N",
	"WATERMARK_COLORS"	=>	array(
		0	=>	"FF0000",
		1	=>	"FFFF00",
		2	=>	"FFFFFF",
		3	=>	"000000",
	),
	"TEMPLATE_LIST"	=>	".default",
	"CELL_COUNT"	=>	"0",
	"SLIDER_COUNT_CELL"	=>	"4",
	"SEF_URL_TEMPLATES"	=>	array(
		"index"	=>	"index.php",
		"galleries"	=>	"galleries/#USER_ID#/",
		"gallery"	=>	"/company/personal/user/#USER_ID#/photo/gallery/#USER_ALIAS#/",
		"gallery_edit"	=>	"/company/personal/user/#USER_ID#/photo/gallery/#USER_ALIAS#/action/#ACTION#/",
		"section"	=>	"/company/personal/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/",
		"section_edit"	=>	"/company/personal/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/action/#ACTION#/",
		"section_edit_icon"	=>	"/company/personal/user/#USER_ID#/photo/album/#USER_ALIAS#/#SECTION_ID#/icon/action/#ACTION#/",
		"upload"	=>	"/company/personal/user/#USER_ID#/photo/photo/#SECTION_ID#/action/upload/",
		"detail"	=>	"/company/personal/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
		"detail_edit"	=>	"/company/personal/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
		"detail_slide_show"	=>	"/company/personal/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
		"detail_list"	=>	"list/",
		"search"	=>	"search/",
		"tags"	=>	"tags/",
	)
	)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>