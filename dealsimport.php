<?
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", 'Y');
define("NO_AGENT_STATISTIC",'Y');
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);

set_time_limit(0);
ini_set('max_execution_time', '0');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

exit();

$dealsCategories = array(
    'Монтаж оборудования, пуско-наладка' => array(
        'ID' => 3,
        'STAGES' => array(
            'Повторный звонок' => 'C3:PREPARATION',
            'Сделка успешна' => 'C3:WON',
            'Сделка провалена' => 'C3:LOSE',
            'В работе' => 'C3:1',
            'Создан заказ' => 'C3:1',
        )
    ),
    'Анализ воды' => array(
        'ID' => 2,
        'STAGES' => array(
            'Повторный звонок' => 'C2:1',
            'Сделка успешна' => 'C2:WON',
            'Сделка провалена' => 'C2:LOSE',
            'В работе' => 'C1:PREPARATION',
            'Создан заказ' => 'C1:PREPARATION',
        )
    ),
    'Продажа товара' => array(
        'ID' => 1,
        'STAGES' => array(
            'Повторный звонок' => 'C1:PREPARATION',
            'В работе' => 'C1:PREPAYMENT_INVOICE',
            'Создан заказ' => 'C1:PREPAYMENT_INVOICE',
            'Сделка успешна' => 'C1:WON',
            'Сделка провалена' => 'C1:LOSE',
        )
    ),
    'Общее' => array(
        'ID' => '',
        'STAGES' => array(
            'Повторный звонок' => 'PREPARATION',
            'В работе' => 'PREPAYMENT_INVOICE',
            'Создан заказ' => 'PREPAYMENT_INVOICE',
            'Сделка успешна' => 'WON',
            'Сделка провалена' => 'LOSE',
        )
    ),
);

$dealsCategories = array();

$fl = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/deals_categories.csv', 'r');

while(!feof($fl)) {
    $data = fgetcsv($fl, 0,';');
    
    if(empty($data[0])) {
        continue;
    }

    $category = $data[0];
    $stage = $data[1];
    $id = $data[2];
    $stageCode = $data[3];

    $dealsCategories[$category]['ID'] = $id;
    $dealsCategories[$category]['STAGES'][$stage] = $stageCode;
}

fclose($fl);

$arOptions = array(
    'IS_RESTORATION' => false,
    'CURRENT_USER' => 1,
    'ENABLE_CLOSE_DATE_SYNC' => false,
);

$stages = array(

);
$dealsSegments = array(
    'Анализы воды - частные лица' => 'SALE',
    'Золотая Формула' => 'COMPLEX',
    'ЗИП - юридические лица' => 'GOODS',
    'Тендер' => 'SERVICES',
    'Анализы воды - юридические лица' => 'SERVICE',
    'Золотая Формула - дилеры' => 1,
    'СОВ - коттеджные' => 2,
    'Сервис / ремонт котеджных СОВ' => 3,
    'СОВ - бытовые' => 4,
    'ЗИП - физические лица' => 5,
    'Сервис / ремонт промышленных СОВ' => 6,
    'СОВ - промышленные' => 7,
    'СОВ - химия, реагенты' => 8,
    'ВХР, ХВП' => 9,
    'Сегмент не определен' => 10,
    'СОВ - засыпки' => 11,
    'Анализы котловой воды' => 12,
    'СОВ - комплектующие' => 13,
    'СОВ - офисные' => 14,
    'Промывка оборудования' => 15,
);

$dealsSegments = array();

$fl = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/deals_types.csv', 'r');

while(!feof($fl)) {
    $data = fgetcsv($fl, 0,';');
    
    if(empty($data[0])) {
        continue;
    }

    $segment = $data[0];
    $id = $data[1];

    $dealsSegments[$segment] = $id;
}

fclose($fl);

$fl = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/deal_2020_3.csv', 'r');

$headers = fgetcsv($fl, 0, ';');

$arOptions = array(
    'IS_RESTORATION' => false,
    'CURRENT_USER' => 1,
    'ENABLE_CLOSE_DATE_SYNC' => false,
);

$fromFileDeals = array();
$fromFileStages = array();
$fromFileCategories = array();
$fromFileSegments = array();
$fromFileAssigned = array();

while(!feof($fl)) {
    $data = fgetcsv($fl, 0,';');
    
    if(empty($data[0])) {
        continue;
    }

    $arData = array_combine($headers, $data);

    if(!in_array($arData['СтадияСделки'], $fromFileStages) && !empty($arData['СтадияСделки'])) {
        $fromFileStages[] = $arData['СтадияСделки'];
    }
    
    if(!in_array($arData['Направление'], $fromFileCategories) && !empty($arData['Направление'])) {
        $fromFileCategories[] = $arData['Направление'];
    }

    if(!in_array($arData['Сегмент'], $fromFileSegments) && !empty($arData['Сегмент'])) {
        $fromFileSegments[] = $arData['Сегмент'];
    }

    if(!in_array($arData['Ответственный'], $fromFileAssigned) && !empty($arData['Ответственный'])) {
        $fromFileAssigned[$arData['Ответственный']] = explode(' ',$arData['Ответственный']);
    }

    $arProduct = array(
        'Товар_ВнешнийКод' => $arData['Товар_ВнешнийКод'],
        'ТипТовар' => $arData['ТипТовар'],
        'Товар' => $arData['Товар'],
        'ТоварХарактеристика' => $arData['ТоварХарактеристика'],
        'Цена' => $arData['Цена'],
        'Количество' => $arData['Количество'],
        'ЕдиницаИзмерения_Код' => $arData['ЕдиницаИзмерения_Код'],
    );
    
    if(empty($fromFileDeals[$arData['НазваниеСделки']])) {
        $arData['PRODUCTS'][$arData['Товар_ВнешнийКод']] = $arProduct;
        unset($arData['ТипТовар'], $arData['Товар'], $arData['ТоварХарактеристика'], $arData['Цена'], $arData['Количество']);
        $fromFileDeals[$arData['НазваниеСделки']] = $arData;
    }
    else {
        $fromFileDeals[$arData['НазваниеСделки']]['PRODUCTS'][$arProduct['Товар_ВнешнийКод']] = $arProduct;
    }
}

foreach($fromFileAssigned as &$fio) {
    $arUser = CUser::GetList($by="ID",$order="asc",array('LAST_NAME'=>$fio[0],'NAME'=>$fio[1],'SECOND_NAME'=>$fio[2]))->Fetch();
    $fio['ID'] = $arUser['ID'];
}

$measures = \Bitrix\Crm\Measure::getMeasures();
$arMeasures = array();

foreach($measures as $m) {
    $arMeasures[$m['CODE']] = $m['ID'];
}

// echo '<pre>', print_r($dealsCategories), '</pre>';
// echo '<pre>', print_r($dealsSegments), '</pre>';
// // echo '<pre>', print_r($fromFileStages), '</pre>';
// // echo '<pre>', print_r($fromFileSegments), '</pre>';
// echo '<pre>', print_r($fromFileAssigned), '</pre>';

// echo '<pre>', print_r($fromFileDeals), '</pre>';

$ccd = new CCrmDeal;

$counter = 0;

foreach($fromFileDeals as $arDeal) {
    $arFields = array(
        'TITLE' => $arDeal['НазваниеСделки'],
        'OPPORTUNITY' => $arDeal['Сумма'],
        'CURRENCY_ID' => 'RUB',
        'ASSIGNED_BY_ID' => $fromFileAssigned[$arDeal['Ответственный']]['ID'],
        'CATEGORY_ID' => $dealsCategories[$arDeal['Направление']]['ID'],
        'STAGE_ID' => $dealsCategories[$arDeal['Направление']]['STAGES'][$arDeal['СтадияСделки']],
        'COMMENTS' => $arDeal['Комментарий'],
        'DATE_CREATE' => $arDeal['ДатаНачала'],
        'DATE_MODIFY' => $arDeal['ДатаНачала'],
        'BEGINDATE' => $arDeal['ДатаНачала'],
        'TYPE_ID' => $dealsSegments[$arDeal['Сегмент']],
        'UF_CRM_1595670027' => $arDeal['ОрганизацияБ24'],
    );
    if($arDeal['Контакт']) {
        $arFields['CONTACT_ID'] = $arDeal['Контакт'];
    }
    if($arDeal['Компания']) {
        $arFields['COMPANY_ID'] = $arDeal['Компания'];
    }
    if($arDeal['ДатаОкончания']) {
        $arFields['CLOSEDATE'] = $arDeal['ДатаОкончания'];
    }

    // $id = $ccd->Add($arFields, true, $arOptions);

    // if($id) {
    //     $arProducts = array();
    //     foreach($arDeal['PRODUCTS'] as $arProduct) {
    //         $arProducts[] = array(
    //             'PRODUCT_NAME' => $arProduct['Товар'],
    //             'PRICE' => $arProduct['Цена'],
    //             'QUANTITY' => $arProduct['Количество'],
    //             'MEASURE_CODE' => $arProduct['ЕдиницаИзмерения_Код'], 
    //         );
    //     }
    //     CCrmDeal::SaveProductRows($id, $arProducts);
    // }

    // echo '<pre>', print_r($arFields), '</pre>';

    // if($counter > 3)
    //     break;

    $counter++;
}

echo 'end';

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");