<?

class WebformProp extends CUserTypeInteger
{

 // инициализация пользовательского свойства для главного модуля
 function GetUserTypeDescription()
 {
  return array(
   "USER_TYPE_ID" => "webform",
   "CLASS_NAME" => "WebformProp",
   "DESCRIPTION" => "Веб-форма",
   "BASE_TYPE" => "int",
  );
 }

 // инициализация пользовательского свойства для инфоблока
 function GetIBlockPropertyDescription()
 {
  return array(
           "PROPERTY_TYPE" => "S",
           "USER_TYPE" => "webform",
           "DESCRIPTION" => "Веб-форма",
   'GetPropertyFieldHtml' => array('WebformProp', 'GetPropertyFieldHtml'),
   'GetAdminListViewHTML' => array('WebformProp', 'GetAdminListViewHTML'),
  );
 }

 // представление свойства
 function getViewHTML($name, $value)
 {
  return '<div style="display: block; width: 16px; height: 16px; background-color: #'.str_pad(dechex($value), 6, '0', STR_PAD_LEFT).';">&nbsp;</div>';
 }

 // редактирование свойства
 function getEditHTML($name, $value, $is_ajax = false)
 {
    $return = "<select name=\"$name\">";
	$return .= '<option value="">Не выбрано</option>';

    if (CModule::IncludeModule("form")) {
        $rsForms = CForm::GetList($sort = "s_sort", $by = "asc", array(), $filtered);
        while($arForm = $rsForms->Fetch()) {
            if($arForm['ID'] == $value) {
                $return .= '<option value="'.$arForm['ID'].'" selected>['.$arForm['ID'].'] '.$arForm['NAME'].'</option>';
            }
            else {
                $return .= '<option value="'.$arForm['ID'].'">['.$arForm['ID'].'] '.$arForm['NAME'].'</option>';
            }
        }
    }

    return $return;
 }

 // редактирование свойства в форме (главный модуль)
 function GetEditFormHTML($arUserField, $arHtmlControl)
 {
  return self::getEditHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], false);
 }

 // редактирование свойства в списке (главный модуль)
 function GetAdminListEditHTML($arUserField, $arHtmlControl)
 {
  return self::getViewHTML($arHtmlControl['NAME'], $arHtmlControl['VALUE'], true);
 }

 // представление свойства в списке (главный модуль, инфоблок)
 function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
 {
  return self::getViewHTML($strHTMLControlName['VALUE'], $value['VALUE']);
 }

 // редактирование свойства в форме и списке (инфоблок)
 function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
 {
  return $strHTMLControlName['MODE'] == 'FORM_FILL'
         ? self::getEditHTML($strHTMLControlName['VALUE'], $value['VALUE'], false)
         : self::getViewHTML($strHTMLControlName['VALUE'], $value['VALUE'])
  ;
 }

}

// добавляем тип для инфоблока
AddEventHandler("iblock", "OnIBlockPropertyBuildList", array("WebformProp", "GetIBlockPropertyDescription"));
// добавляем тип для главного модуля
AddEventHandler("main", "OnUserTypeBuildList", array("WebformProp", "GetUserTypeDescription"));