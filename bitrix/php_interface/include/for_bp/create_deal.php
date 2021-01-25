<?
use Bitrix\Crm;
use Bitrix\Crm\Binding;
use Bitrix\Sale\Helpers\Order\Builder;

class CrkrBPDealFromOrder {

    var $parameters = array(),
    $order,
    $deal,
    $dealId,
    $siteId = 's2',
    $personType1, // Физическое лицо
    $personType2; // Юридическое лицо

    function __construct ($orderId, $personType1, $personType2, $siteId = 's1') {
        \Bitrix\Main\Loader::includeModule('crm');
        \Bitrix\Main\Loader::includeModule('sale');
        $this->personType1 = $personType1;
        $this->personType2 = $personType2;
        $this->order = \Bitrix\Sale\Order::load($orderId);
    }

    function createDeal () {
        if($this->order->getSiteId() == $this->siteId) {
            $arDealFields = array(
        
            );

            $propertyCollection = $this->order->getPropertyCollection();
            $arProperties = $propertyCollection->getArray()['properties'];
        
            $personTypeId = $this->order->getPersonTypeId();

            if($personTypeId == $personType2) {
                $codes = array(
                    'CONTACT',
                    'CONTACT_ADR',
                    'CONTACT_PERSON',
                    'INN',
                    'KPP',
                    'EMAIL',
                    'PHONE',
                );
                $arProperties = $this->getProperties($codes, $arProperties);
                $arProperties = array_merge($arProperties, $this->prepareFIO($arProperties['CONTACT_PERSON']));
                $contactId = $this->createContact($this->order->getUserId(), $arProperties);
                $companyId = $this->createCompany($contactId, $arProperties);
            }
            else {
                $codes = array(
                    'FIO',
                    'EMAIL',
                    'PHONE',
                );
                $arProperties = $this->getProperties($codes, $arProperties);
                $arProperties = array_merge($arProperties, $this->prepareFIO($arProperties['FIO']));
                $contactId = $this->createContact($this->order->getUserId(), $arProperties);
            }
        }
    }

    function createContact ($userId, $arProperties) {

        print_r($arProperties);

        $arUser = \Bitrix\Main\UserTable::getList(array('filter'=>array('ID'=>$userId)))->fetch();

        $userXMLID = $arUser['XML_ID']; // поиск по внешнему коду

        if(!empty($userXMLID)) {
            $arContact = CCrmContact::GetList(array(),array('ORIGIN_ID'=>$userXMLID))->Fetch();
        }

        $arProperties['PHONE'] = preg_replace('/[^0-9]/', '', $arProperties['PHONE']);

        if(!$arContact && !empty($arProperties['PHONE'])) { // поиск по номеру телефона
            $arFieldMulti = CCrmFieldMulti::GetList(
                array(),
                array('ENTITY_ID'=>'CONTACT', 'TYPE'=>'PHONE', 'VALUE'=>$arProperties['PHONE'])
            )->Fetch();
            $contactId = $arFieldMulti['ELEMENT_ID'];
            if($contactId) {
                $arContact = CCrmContact::GetList(array(),array('ID'=>$contactId))->Fetch();
            }
        }

        if(!$arContact && !empty($arProperties['EMAIL'])) { // поиск по электронной почте
            $arFieldMulti = CCrmFieldMulti::GetList(
                array(),
                array('ENTITY_ID'=>'CONTACT', 'TYPE'=>'EMAIL', 'VALUE'=>$arProperties['EMAIL'])
            )->Fetch();
            $contactId = $arFieldMulti['ELEMENT_ID'];
            if($contactId) {
                $arContact = CCrmContact::GetList(array(),array('ID'=>$contactId))->Fetch();
            }
        }

        if($arContact) {
            return $arContact['ID'];
        }
        else { // если совсем ничего не нашлось, создаём контакт
            $ccrmcontact = new CCrmContact;
            $arFields = array(
                "NAME" => $arProperties['NAME'],
                "LAST_NAME" => $arProperties['LAST_NAME'],
                "SECOND_NAME" => $arProperties['SECOND_NAME'],
                "OPENED" => "Y",
                "EXPORT" => "Y",
                'FM' => array(
                    'EMAIL' => array(
                        'n0' => array('VALUE' => $arProperties['EMAIL'], 'VALUE_TYPE' => 'WORK')
                    ),
                    'PHONE' => array(
                        'n0' => array('VALUE' => $arProperties['PHONE'], 'VALUE_TYPE' => 'WORK')
                    ) 
                ),
                "ASSIGNED_BY_ID" => "1",
            );
            $oContact = new CCrmContact;
            //$contactId = $oContact->Add($arFields);
            return $contactId;
        }
    }

    function createCompany ($contactId, $arProperties) {
        $arFieldsComp = array(
            'TITLE' => "МММ",
            'CONTACT_ID' => array($contactId),
        );

        $CCrmCompany = new CCrmCompany();
        // $companyId = $CCrmCompany->Add($arFieldsComp);
    }

    function getProperties ($codes, $arProperties) {
        $arResult = array();
        foreach($arProperties as $arProp) {
            if(in_array($arProp['CODE'], $codes)) {
                if(count($arProp['VALUE']) == 1 && !empty($arProp['VALUE'][0])) {
                    $arResult[$arProp['CODE']] = $arProp['VALUE'][0];
                }
                else {
                    $arResult[$arProp['CODE']] = $arProp['VALUE'];
                }
            }
        }
        return $arResult;
    }

    function prepareFIO ($value) {
        $ids = array('LAST_NAME', 'NAME', 'SECOND_NAME');
        return array_combine($ids, explode(' ', $value));
    }
}