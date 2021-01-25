<?
use Bitrix\Crm;
use Bitrix\Crm\Binding;
use Bitrix\Sale\Helpers\Order\Builder;

class CrkrBPOrder {

var $parameters = array(),
    $order,
    $deal,
    $basket,
    $dealId,
    $siteId,
    $personType1, // Физическое лицо
    $personType2, // Юридическое лицо
    $userId,
    $paysystemId,
    $paysystemForCompany = 15,
    $deliveryId = 4,
    $checkFieldsResult,
    $userData,
    $userGroup = 2; // группа Все пользователи

    function __construct ($dealId, $personType1, $personType2, $siteId, $paysystemId) {
        \Bitrix\Main\Loader::includeModule('crm');
        \Bitrix\Main\Loader::includeModule('sale');
        $this->siteId = $siteId;
        $this->personType1 = $personType1;
        $this->personType2 = $personType2;
        $this->dealId = $dealId;
        $this->paysystemId = $paysystemId;
        $this->deal = CCrmDeal::GetByID($this->dealId);
    }

    function loadFromDeal ($dealId = false) {

        if($dealId === false) {
            $dealId = $this->dealId;
        }

        $orderId = \Bitrix\Crm\Binding\OrderDealTable::getList(array('filter'=>array('DEAL_ID' => $dealId), 'order'=>array('ORDER_ID'=>'ASC')))->fetch()['ORDER_ID'];
        if($orderId) {
            $this->order = \Bitrix\Sale\Order::load($orderId);
            return true;
        }
        else
            return false;
    }

    function getPerson () {
        if ($this->deal['COMPANY_ID']) {
            return $this->personType2;
        }
        else {
            return $this->personType1;
        }
    }

    function getUserForOrder () {

        $arContact = CCrmContact::GetByID($this->deal['CONTACT_ID']);
        $rsContactData = CCrmFieldMulti::GetList(
            array(),
            array('ELEMENT_ID' => $arContact['ID'], 'ENTITY_ID' => 'CONTACT')
        );

        $phone = $email = '';
        while($arContactData = $rsContactData->Fetch()) {
            if($arContactData['TYPE_ID'] == 'PHONE') {
                $phone = $arContactData['VALUE'];
            }
            if($arContactData['TYPE_ID'] == 'EMAIL') {
                $email = $arContactData['VALUE'];
            }
        }

        $arUser = \Bitrix\Main\UserTable::getList(array('filter'=>array('EMAIL'=>$email)))->fetch();

        if($arUser) {
            $this->userData = array(
                'NAME' => $arUser['NAME'],
                'LAST_NAME' => $arUser['LAST_NAME'],
                'EMAIL' => $arUser['EMAIL'],
                'PHONE' => $arUser['PERSONAL_PHONE']
            );
            if($arUser['XML_ID'] != $arContact['ORIGIN_ID']) {
                $user = new CUser;
                $userId = $user->Update($arUser['ID'],array('XML_ID'=>$arContact['ORIGIN_ID']));
            }
            return $arUser['ID'];
        }
        else {

            $new_password = randString(7, array(
                "abcdefghijklnmopqrstuvwxyz",
                "ABCDEFGHIJKLNMOPQRSTUVWX­YZ",
                "0123456789",
                "!@#\$%^&*()",
            ));

            $user = new CUser;
            $arFields = Array(
                "NAME"              => $arContact['NAME'],
                "LAST_NAME"         => $arContact['LAST_NAME'],
                "EMAIL"             => $email, // обязатель 
                "LOGIN"             => $email, // обязатель
                "LID"               => $this->siteId,
                "PERSONAL_PHONE"    => $phone,
                "ACTIVE"            => "Y",
                "GROUP_ID"          => $this->userGroup,
                "PASSWORD"          => $new_password, // обязатель
                "CONFIRM_PASSWORD"  => $new_password, // обязатель
                "XML_ID"            => $arContact['ORIGIN_ID'],
            );
                    
            $userId = $user->Add($arFields);
            if (intval($userId) > 0){
                $this->userData = array(
                    'NAME' => $arContact['NAME'],
                    'LAST_NAME' => $arContact['LAST_NAME'],
                    'EMAIL' => $email,
                    'PHONE' => $phone
                );
                return $userId;
            }
            else{
                $user_errors['new_user'] = $user->LAST_ERROR;
                return false;
            }
        }
    }

    function createBasketByDeal() {
        $arProducts = \CCrmDeal::LoadProductRows($this->dealId);

        // if($arProducts) {

        //     $basket = \Bitrix\Sale\Basket::create($this->siteId);

        //     foreach ($arProducts as $arProduct)
        //     {
        //         $basketItem = $basket->createItem("catalog", $arProduct["PRODUCT_ID"]);
        //         $products = array('PRODUCT_ID' => $arProduct["PRODUCT_ID"], 'NAME' => $arProduct["PRODUCT_NAME"], 'PRICE' => $arProduct["PRICE"], 'CURRENCY' => 'RUB', 'QUANTITY' => $arProduct["QUANTITY"], 'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider', 'LID' => $this->siteId);
        //         $basketItem->setFields($products);
        //     }

        //     $basket->save();

        //     $this->basket = $basket;

        //     return $basket;
        // }

        $basket = \Bitrix\Sale\Basket::create($this->siteId);

        $basketItem = $basket->createItem("catalog", 1);
        $products = array('PRODUCT_ID' => 1, 'NAME' => '', 'PRICE' => 0, 'CURRENCY' => '', 'QUANTITY' => 1, 'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider', 'LID' => $this->siteId);
        $basketItem->setFields($products);

        $basket->save();

        $this->basket = $basket;

        return $basket;
    }

    function checkFieldsInDeal() {
        // $arProducts = \CCrmDeal::LoadProductRows($this->dealId);

        // if(!$arProducts) {
        //     $this->checkFieldsResult[] = 'К сделке не привязан ни один товар';
        // }

        if(!$this->deal['CONTACT_ID']) {
            $this->checkFieldsResult[] = 'К сделке не привязан ни один контакт';
        }

        if(count($this->checkFieldsResult)) {
            return false;
        }
        else {
            return true;
        }
    }

    function changeStatus($statusId) {

        if(!$this->order) {
            $this->loadFromDeal();
        }
        
        if($this->order) {

            $this->order->setField('STATUS_ID', $statusId);
            $this->order->save();
        }
    }

    function statusPaidFull() {
        if($this->order->isPaid()) {
            return true;
        }
        else {
            return false;
        }
    }

    function statusPaidPart() {
        if($this->order->getSumPaid()) {
            return true;
        }
        else {
            return false;
        }
    }

    function getOrderDelivery() {
        return $this->order->getDeliverySystemId();
    }

    function createOrderByDeal() {

        $userId = $this->getUserForOrder();

        // $order = Bitrix\Sale\Order::create($this->siteId, $userId);
        // $order->setPersonTypeId($this->getPerson());
        $this->createBasketByDeal();
        // $order->setBasket($this->basket);

        $formData = [
			'SITE_ID' => $this->siteId,
		];
        $formData['USER_ID'] = $userId;
        $clientInfo = array(
            'USER_ID' => $userId,
            'COMPANY_ID' => $this->deal['COMPANY_ID'],
            'CONTACT_IDS' => array($this->deal['CONTACT_ID']),
        );
        $formData['CLIENT'] = $clientInfo;

        $settings =	[
			'createUserIfNeed' => '',
			'acceptableErrorCodes' => [],
			'cacheProductProviderData' => true,
		];
		$builderSettings = new Builder\SettingsContainer($settings);
		$orderBuilder = new Crm\Order\OrderBuilderCrm($builderSettings);
		$director = new Builder\Director;
		$order = $director->createOrder($orderBuilder, $formData);

		if ($order)
		{
            $personId = $this->getPerson();
            $order->setPersonTypeId($personId);
			$order->setBasket($this->basket);
			// $order->getDealBinding()->setDealId($this->dealId);
        }
        
        $paysystemId = $this->paysystemId;
        if($personId == $this->personType2) {
            $paysystemId = $this->paysystemForCompany;
        }
        
        $order->setField('PRICE', $this->basket->getPrice());
        $order->setField('RESPONSIBLE_ID', $this->deal['ASSIGNED_BY_ID']);

        $this->order = $order;

        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem(
            \Bitrix\Sale\Delivery\Services\Manager::getObjectById($this->deliveryId)
        );

        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        foreach ($this->basket as $basketItem)
        {
            $item = $shipmentItemCollection->createItem($basketItem);
            $item->setQuantity($basketItem->getQuantity());
        }

        $paymentCollection = $order->getPaymentCollection();
        $payment = $paymentCollection->createItem(
            \Bitrix\Sale\PaySystem\Manager::getObjectById($paysystemId)
        );

        $payment->setField("SUM", $this->basket->getPrice());
        $payment->setField("CURRENCY", $order->getCurrency());

        $propertyCollection = $order->getPropertyCollection();
        // телефон
        // $phoneProp = $propertyCollection->getPhone();
        // $phoneProp->setValue($this->userData['PHONE']);
        // имя
        $nameProp = $propertyCollection->getPayerName();
        $nameProp->setValue($this->userData['NAME'].' '.$this->userData['LAST_NAME']);
        // email
        $emailProp = $propertyCollection->getUserEmail();
        $emailProp->setValue($this->userData['EMAIL']);

        // echo '<pre>', print_r($order), '</pre>';

        $result = $order->save();

        if($result->isSuccess()) {
            \Bitrix\Crm\Binding\OrderDealTable::add(array('DEAL_ID' => $this->dealId, 'ORDER_ID' => $order->getId()));
            // \Bitrix\Crm\Binding\OrderContactCompanyTable::add(array(
            //     'ORDER_ID' => $order->getId(),
            //     'ENTITY_ID' => $this->deal['CONTACT_ID'],
            //     'ENTITY_TYPE_ID' => $this->personType1,
            //     'SORT' => 0,
            //     'ROLE_ID' => 0,
            //     'IS_PRIMARY' => 'Y'
            // ));
            // if ($this->deal['COMPANY_ID']) {
            //     \Bitrix\Crm\Binding\OrderContactCompanyTable::add(array(
            //         'ORDER_ID' => $order->getId(),
            //         'ENTITY_ID' => $this->deal['COMPANY_ID'],
            //         'ENTITY_TYPE_ID' => $this->personType2,
            //         'SORT' => 0,
            //         'ROLE_ID' => 0,
            //         'IS_PRIMARY' => 'Y'
            //     ));
            // }
            return $order->getId();
        }
        else {
            // return $result->getErrors();
            return false;
        }
    }

}