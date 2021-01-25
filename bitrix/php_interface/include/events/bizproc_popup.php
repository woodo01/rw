<?

class CYourModulePullSchema
{
    public static function OnGetDependentModule()
    {
        return Array(
            'MODULE_ID' => "crkr.bizproc",
            'USE' => Array("PUBLIC_SECTION")
     );
     }
}

AddEventHandler("main", "OnEpilog", "addJSforBPPopup");

function addJSforBPPopup () {
    if(SITE_ID == 's2') {
        CModule::IncludeModule('bizproc');
        CJSCore::init(array('bp_starter'));
        $GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/bizproc/tools.js');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/js/bizproc_popup.js');
    }
}

AddEventHandler("bizproc", "OnTaskAdd", "sendPush");

function sendPush($id, $arFields) {
    CModule::IncludeModule('pull');

    CModule::IncludeModule('bizproc');

    $rs = CBPTaskService::GetList(array(),array('ID'=>$id), false, false, array('ID','NAME','USER_ID'));

    while($ar = $rs->fetch()) {
        if(is_array($ar['USER_ID'])) {
            foreach($ar['USER_ID'] as $userId) {
                CPullStack::AddByUser(
                    $userId, Array(
                        'module_id' => 'crkr.bizproc',
                        'command' => 'showPopup',
                        'params' => Array('ID'=>$id),
                    )
                );
            }
        }
        else {
            CPullStack::AddByUser(
                $ar['USER_ID'], Array(
                    'module_id' => 'crkr.bizproc',
                    'command' => 'showPopup',
                    'params' => Array('ID'=>$id),
                )
            );
        }
    }
}