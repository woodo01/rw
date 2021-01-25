<?

AddEventHandler('crm', 'OnBeforeCrmDealUpdate', 'checkCategory');
AddEventHandler('crm', 'OnAfterCrmDealUpdate', 'startBP');

$eventChangedTo = false;
$eventDealId = false;

function checkCategory(&$arFields) {
    $arDeal = CCrmDeal::GetByID($arFields['ID']);

    if(!empty($arFields['CATEGORY_ID']) && $arFields['CATEGORY_ID'] != $arDeal['CATEGORY_ID']) {
        $eventChangedTo = $arFields['CATEGORY_ID'];
        $eventDealId = $arFields['ID'];
    }

    // \Bitrix\Main\Diag\Debug::writeToFile(array_merge(array('FILE'=>'deals_categories.php'), $arFields), '', '/crm-deal.log');
}

function startBp ($arFields) {

    $dealInfo = array(
        4 => array(
            'STAGE_ID' => 'C4:NEW',
            'BP' => 66
        ),
        3 => array(
            'STAGE_ID' => 'C3:NEW',
            'BP' => 67
        ),
        2 => array(
            'STAGE_ID' => 'C2:NEW',
            'BP' => 88
        )
    );

    if($eventDealId == $arFields['ID'] && $eventChangedTo) {
        $deal = new CCrmDeal;

        $deal->Update(
            $arFields['ID'],
            array(
                'STAGE_ID' => $dealInfo[$stagedTo]['STAGE_ID'],
                'MODIFY_BY_ID' => 1
            ),
            true,
            true,
            array('DISABLE_USER_FIELD_CHECK' => true)
        );

        CModule::IncludeModule('bizproc');

        CBPDocument::StartWorkflow(
            $dealInfo[$changedTo]['BP'],
            array("bizproc","CBPVirtualDocument",$arFields['ID']),
            array(),
            $arErrorsTmp
        );
        // \Bitrix\Main\Diag\Debug::writeToFile(array_merge(array('FILE'=>'deals_categories.php'), $arFields), '', '/crm-deal.log');
    }
}