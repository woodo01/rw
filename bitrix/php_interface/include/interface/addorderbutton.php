<?
global $APPLICATION;

if(CSite::InDir('/crm/deal/details/')) {
    \Bitrix\Main\UI\Extension::load('ui.buttons');
    \Bitrix\Main\UI\Extension::load('ui.buttons.icons');

    $id = explode("/", $APPLICATION->GetCurPage());
    $id = array_diff($id, array(''));
    $id = end($id);

    ob_start();
    /*
    <a class="ui-btn ui-btn-light-border ui-btn-icon-add" href="/bitrix/components/bitrix/crm.order.list/lazyload.ajax.php?order_id=0&edit" title="Добавить заказ" onclick="BX.SidePanel.Instance.open('/shop/orders/details/0/?deal_id=<?=$id?>&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER',{'CLIENT_INFO':{'DEAL_ID':<?=$id?>}}); return false;"><span class="crm-toolbar-btn-icon"></span><span>Заказ</span></a>*/?>
    <a class="ui-btn ui-btn-primary ui-btn-icon-add" href="/local/createorderfordeal.php?deal_id=<?=$id?>" title="Добавить заказ" onclick="BX.ajax({url:'/local/createorderfordeal.php',method:'post',data:'deal_id=<?=$id?>',dataType:'json',onsuccess:function(data){ if(data.id){BX.SidePanel.Instance.open('/shop/orders/details/'+data.id+'/');} else {crkrShowPopup(data.error);} } }); return false;"><span class="crm-toolbar-btn-icon"></span><span>Заказ</span></a>
    <?
    $html = ob_get_clean();
    $APPLICATION->AddViewContent('inside_pagetitle', $html, 1500);
}

// $eventManager = \Bitrix\Main\EventManager::getInstance();
// $eventManager->addEventHandlerCompatible('main', 'OnAfterEpilog', 'dumpViews');

function dumpViews () {
    global $APPLICATION;
    var_dump($APPLICATION->__view);

    $contect = \Bitrix\Main\Context::getCurrent();
    $request = $contect->getRequest();
}