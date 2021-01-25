<?

// AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("CRKREventDimensions", "OnAfterIBlockElementAddHandler"));
// AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("CRKREventDimensions", "OnAfterIBlockElementUpdateHandler"));

// class CRKREventDimensions
// {
//     function OnAfterIBlockElementUpdateHandler(&$arFields)
//     {
//         CRKREventDimensions::UpdateDimensions($arFields['ID']);
//     }

//     function OnAfterIBlockElementAddHandler(&$arFields) {
//         CRKREventDimensions::UpdateDimensions($arFields['ID']);
//     }

//     function UpdateDimensions($id) {
//         if(CModule::IncludeModule('iblock')) {
//             $codes = array('DLINA', 'SHIRINA', 'VYSOTA');
//             $arElement = CIBlockElement::GetList(array(),array('ID'=>$id),false,false,array('ID','PROPERTY_DLINA','PROPERTY_SHIRINA','PROPERTY_VYSOTA'))->Fetch();
//             \Bitrix\Main\Diag\Debug::writeToFile($arElement);
//             if(CCatalogProduct::GetByID($id)) {
//                 if(CCatalogProduct::Update($id, array(
//                     'WIDTH' => intval($arElement['PROPERTY_SHIRINA_VALUE']),
//                     'LENGTH' => intval($arElement['PROPERTY_DLINA_VALUE']),
//                     'HEIGHT' => intval($arElement['PROPERTY_VYSOTA_VALUE']),
//                     'MEASURE' => 1
//                 )))
//                 \Bitrix\Main\Diag\Debug::writeToFile('updated');
//                 \Bitrix\Main\Diag\Debug::writeToFile(CCatalogProduct::GetByID($id));
//             }
//         }
//     }
// }


AddEventHandler("catalog", "OnBeforeProductUpdate", 'setDimensionsUpdate');

function setDimensionsUpdate($id, &$arFields){
    $addProps = CIBlockElement::GetList (
        Array("ID" => "ASC"),
        Array("ID" => $id),
        false,
        false,
        Array('ID','PROPERTY_DLINA','PROPERTY_SHIRINA','PROPERTY_VYSOTA')
    );

    while($ar_fields = $addProps->GetNext())
    {
        $width = $ar_fields["PROPERTY_SHIRINA_VALUE"];
        $height = $ar_fields["PROPERTY_VYSOTA_VALUE"];
        $length = $ar_fields["PROPERTY_DLINA_VALUE"];
    }

    $arFields["WIDTH"] = $width;
    $arFields["HEIGHT"] = $height;
    $arFields["LENGTH"] = $length;
}

AddEventHandler("catalog", "OnBeforeProductAdd", 'setDimensionsAdd');

function setDimensionsAdd(&$arFields){
    $addProps = CIBlockElement::GetList (
        Array("ID" => "ASC"),
        Array("ID" => $arFields['ID']),
        false,
        false,
        Array('ID','PROPERTY_DLINA','PROPERTY_SHIRINA','PROPERTY_VYSOTA')
    );

    while($ar_fields = $addProps->GetNext())
    {
        $width = $ar_fields["PROPERTY_SHIRINA_VALUE"];
        $height = $ar_fields["PROPERTY_VYSOTA_VALUE"];
        $length = $ar_fields["PROPERTY_DLINA_VALUE"];
    }

    $arFields["WIDTH"] = $width;
    $arFields["HEIGHT"] = $height;
    $arFields["LENGTH"] = $length;
}