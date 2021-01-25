<?

AddEventHandler('crm', 'OnBeforeCrmDealUpdate', 'checkStage');

function checkStage(&$arFields) {

    $bAdmin = false;

    $modifyById = $arFields['MODIFY_BY_ID'];

    if(in_array(1, CUser::GetUserGroup($modifyById)) || in_array(1, $GLOBALS['USER']->GetUserGroupArray()))
        $bAdmin = true;

    if(!$bAdmin) {

        \Bitrix\Main\Loader::includeModule('bizproc');
        \Bitrix\Main\Loader::includeModule('tasks');
        \Bitrix\Main\Loader::IncludeModule('pull');
        
        $bp = \Bitrix\Bizproc\WorkflowStateTable::getList(array('filter'=>array('DOCUMENT_ID'=>'DEAL_'.$arFields['ID'], '!=STATE'=>'Completed')))->fetch();
        if($bp) {
            $task = $rs = CBPTaskService::GetList(array(),array('WORKFLOW_ID'=>$bp['ID'], '!=STATUS'=>CTasks::STATE_COMPLETED), false, false, array('ID','NAME','STATUS'))->Fetch();
            if($task && $task['STATUS'] != 3) {
                global $APPLICATION;

                CPullStack::AddByUser(
                    $GLOBALS['USER']->GetID(), Array(
                        'module_id' => 'crkr.bizproc',
                        'command' => 'showStageControlError',
                        'params' => Array('message'=>'По сделке есть незаврешённая задача: '.$task['NAME'].'. Смена стадии отменена.'),
                    )
                );

                $arFields['RESULT_MESSAGE'] = 'По сделке есть незаврешённая задача: '.$task['NAME'].'. Смена стадии отменена.';
                $APPLICATION->throwException('По сделке есть незаврешённая задача: '.$task['NAME'].'. Смена стадии отменена.');
                return false;
            }
        }
    
        $arDeal = CCrmDeal::GetByID($arFields['ID']);

        $acceptedStages = array( // массив в формате "Старый статус" => "Новый статус"
            'C2:PREPAYMENT_INVOICE' => 'C2:3',
            'C1:3' => 'C1:PREPAYMENT_INVOICE',
            'C1:PREPAYMENT_INVOICE' => 'C1:1',
        );

        if(!empty($arFields['STAGE_ID']) && stripos($arFields['STAGE_ID'], 'LOSE') === false && stripos($arFields['STAGE_ID'], 'APOLOGY') === false) {
            if(empty($arFields['CATEGORY_ID']) || $arFields['CATEGORY_ID'] == $arDeal['CATEGORY_ID']) {
                $rsStatuses = CCrmStatus::GetList(array('SORT'=>'ASC'),array('ENTITY_ID'=>'DEAL_STAGE_'.$arDeal['CATEGORY_ID']));

                $arStatuses = array();
                
                while($arStatus = $rsStatuses->Fetch()) {
                    $arStatuses[] = $arStatus['STATUS_ID'];
                }

                $keyNew = array_search($arFields['STAGE_ID'], $arStatuses);
                $keyOld = array_search($arDeal['STAGE_ID'], $arStatuses);

                if($keyNew > 2 && $keyOld != ($keyNew-1) 
                    && (empty($acceptedStages[$arDeal['STAGE_ID']]) || $acceptedStages[$arDeal['STAGE_ID']] != $arFields['STAGE_ID'])) {

                    global $APPLICATION;

                    CPullStack::AddByUser(
                        $GLOBALS['USER']->GetID(), Array(
                            'module_id' => 'crkr.bizproc',
                            'command' => 'showStageControlError',
                            'params' => Array('message'=>'Переходы по стадиям должны быть последовательными. Смена стадии отменена.'),
                        )
                    );

                    $arFields['RESULT_MESSAGE'] = 'Переходы по стадиям должны быть последовательными. Смена стадии отменена.';
                    $APPLICATION->throwException("Переходы по стадиям должны быть последовательными. Смена стадии отменена.");
                    return false;
                }
            }
        }

        // if(stripos($arFields['STAGE_ID'], 'WON')) {
        //     $paysystemId = 1;

        //     $crmOrder = new CrkrBPOrder($arFields['ID'], 3, 4, 's2', $paysystemId);
        //     $crmOrder->loadFromDeal();
        //     if(!$crmOrder->statusPaidFull()) {
        //         global $APPLICATION;

        //         CPullStack::AddByUser(
        //             $GLOBALS['USER']->GetID(), Array(
        //                 'module_id' => 'crkr.bizproc',
        //                 'command' => 'showStageControlError',
        //                 'params' => Array('message'=>'Заказ в сделке не оплачен. Смена стадии отменена.'),
        //             )
        //         );

        //         $arFields['RESULT_MESSAGE'] = 'Заказ в сделке не оплачен. Смена стадии отменена.';
        //         $APPLICATION->throwException("Заказ в сделке не оплачен. Смена стадии отменена.");
        //         return false;
        //     }
        // }
    }
}