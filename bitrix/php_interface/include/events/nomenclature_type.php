<?
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "setNomenclatureType");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "setNomenclatureType");


function setNomenclatureType ($arFields) {

    $IBLOCK_ID = $arFields['IBLOCK_ID'];
    $ELEMENT_ID = $arFields['ID'];

    $arElement = CIBlockElement::GetList(array(),array('ID'=>$ELEMENT_ID,'IBLOCK_ID'=>$IBLOCK_ID),false,false,array('ID','IBLOCK_ID'))->GetNextElement();

    $arProps = $arElement->GetProperties();

    $key = array_search('ТипНоменклатуры', $arProps['CML2_TRAITS']['DESCRIPTION']);

    if($key !== false) {

        $inTraits = $arProps['CML2_TRAITS']['VALUE'][$key];
        
        if($inTraits != $arProps['NOMENCLATURE_TYPE']['VALUE']) {
            CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, $IBLOCK_ID, array('NOMENCLATURE_TYPE'=>$inTraits));
        }
    }

}