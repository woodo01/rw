<?require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['deal_id']) {
$dealId = intval($_POST['deal_id']);
$siteId = 's2';
$personType1 = 3;
$personType2 = 4;
$paysystemId = '6';

$class = new CrkrBPOrder($dealId, $personType1, $personType2, $siteId, $paysystemId);
$checkResult = $class->checkFieldsInDeal();

    if($checkResult) {
        $orderId = $class->createOrderByDeal();
        if($orderId) {
            echo json_encode(array('id'=>$orderId));
        }
        else {
            echo json_encode(array('error'=>'Произошла ошибка при создании заказа.'));
        }
    }
    else {
        echo json_encode(array('error'=>'Нельзя создать заказ без Контакта в сделке.'));
    }
}
else {
    echo json_encode(array('error'=>'Функционал пока недоступен.'));
}