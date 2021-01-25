<?

use Bitrix\Main;
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'changeDeal'
);

function changeDeal(Main\Event $event)
{
    \Bitrix\Main\Loader::includeModule('crm');

    $statuses = array(
        '3' => array(
            'U' => 'C3:3'
        )
    );

    $oldValues = $event->getParameter('VALUES');
    $order = $event->getParameter('ENTITY');

    // \Bitrix\Main\Diag\Debug::writeToFile($oldValues, '', $_SERVER['DOCUMENT_ROOT'].'/crm-order.log');


    if($oldValues['STATUS_ID'] != $order->getField('STATUS_ID')) {

        $dealId = \Bitrix\Crm\Binding\OrderDealTable::getList(array('filter'=>array('ORDER_ID' => $order->getId())))->fetch()['DEAL_ID'];

        if($dealId) {
            $deal = CCrmDeal::GetByID($dealId);

            $categoryId = $deal['CATEGORY_ID'];

            if($stage = $statuses[$categoryId][$order->getField('STATUS_ID')]) {
                $deal = new CCrmDeal;

                $arUpdateData = array(
                    'STAGE_ID' => $stage
                );

                $deal->Update(
                    $dealId,
                    $arUpdateData,
                    true,
                    true,
                    array('DISABLE_USER_FIELD_CHECK' => true)
                );
            }
        }
    }

    if($oldValues['PRICE'] != $order->getPrice()) {
        $dealId = \Bitrix\Crm\Binding\OrderDealTable::getList(array('filter'=>array('ORDER_ID' => $order->getId())))->Fetch()['DEAL_ID'];

        $sum = 0;

        $arProducts = array();

        $rsDeal = \Bitrix\Crm\Binding\OrderDealTable::getList(array('filter'=>array('DEAL_ID' => $dealId)));
        while($arDeal = $rsDeal->Fetch()) {
            $tmpOrder = \Bitrix\Sale\Order::load($arDeal['ORDER_ID']);
            $basket = $tmpOrder->getBasket();
            foreach($basket as $basketItem) {
                $arProducts[] = array(
                    'PRODUCT_ID' => $basketItem->getProductId(),
                    'PRODUCT_NAME' => $basketItem->getField('NAME'),
                    'PRICE' => $basketItem->getPrice(),
                    'QUANTITY' => $basketItem->getQuantity(),
                );
            }
            $sum += $tmpOrder->getPrice();
        }
        $deal = CCrmDeal::GetByID($dealId);

        $deal = new CCrmDeal;

        $arUpdateData = array(
            'OPPORTUNITY' => $sum
        );

        $deal->Update(
            $dealId,
            $arUpdateData,
            true,
            true,
            array('DISABLE_USER_FIELD_CHECK' => true)
        );

        CCrmDeal::SaveProductRows($dealId, $arProducts);

        // \Bitrix\Main\Diag\Debug::writeToFile($dealId, '', $_SERVER['DOCUMENT_ROOT'].'/crm-order.log');
        // \Bitrix\Main\Diag\Debug::writeToFile($sum, '', $_SERVER['DOCUMENT_ROOT'].'/crm-order.log');
    }
}