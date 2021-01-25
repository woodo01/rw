<?php

function crkrPushPull ($userId, $command, $arParams) {
    CModule::IncludeModule('pull');

    \Bitrix\Main\Diag\Debug::writeToFile(array($userId, $command, $arParams),"","bp.txt");

    if(preg_match('/user/', $userId)) {
        $userId = preg_replace('/[^0-9]/','',$userId);
    }

    CPullStack::AddByUser(
        $userId, Array(
            'module_id' => 'crkr.bizproc',
            'command' => $command,
            'params' => $arParams
        )
    );
}