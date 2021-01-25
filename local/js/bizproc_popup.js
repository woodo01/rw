BX.addCustomEvent("onPullEvent", function(module_id,command,params) {
    console.log(module_id);
    console.log(command);
    console.log(params);
    if (command == 'showPopup' && module_id == 'crkr.bizproc')
    {
        BX.Bizproc.showTaskPopup(params.ID);
    }
    if (command == 'showStageControlError' && module_id == 'crkr.bizproc') {
        var oPopup = new BX.PopupWindow('crkr_stagecontrol', window.body, {
            width:400,
            autoHide : true,
            offsetTop : 1,
            offsetLeft : 0,
            lightShadow : true,
            closeIcon : true,
            closeByEsc : true,
            overlay: {
                backgroundColor: 'black', opacity: '80'
            },
            buttons: [
                  new BX.PopupWindowButton({
                      text: "Перезагрузить страницу",
                      className: "popup-window-button-accept",
                      events: {click: function(){
                       location.reload();
                      }}
                  }),
            ],
            titleBar: 'Ошибка',
            className: 'popup-window popup-window-content-white popup-window-with-titlebar popup-window-fixed-width',
            });
        oPopup.setContent(params.message);
        oPopup.show();
        
    }
    if(command == 'openOrderEdit' && module_id == 'crkr.bizproc') {
        BX.SidePanel.Instance.open('https://crm.ruswater.com/shop/orders/details/'+params.ID+'/');
    }
    if(command == 'openSendMail' && module_id == 'crkr.bizproc') {
        BX.SidePanel.Instance.open('https://crm.ruswater.com/bitrix/components/bitrix/crm.activity.planner/slider.php?site_id=s2&context=deal-'+params.ID+'&ajax_action=ACTIVITY_EDIT&activity_id=0&TYPE_ID=4&OWNER_ID='+params.ID+'&OWNER_TYPE=DEAL&OWNER_PSID=0&FROM_ACTIVITY_ID=0&MESSAGE_TYPE=&__post_data_hash=0&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER');
    }
});
function crkrShowPopup(message) {
    var oPopup = new BX.PopupWindow('crkr_stagecontrol', window.body, {
        width:400,
        autoHide : true,
        offsetTop : 1,
        offsetLeft : 0,
        lightShadow : true,
        closeIcon : true,
        closeByEsc : true,
        overlay: {
            backgroundColor: 'black', opacity: '80'
        },
        titleBar: 'Информация',
        className: 'popup-window popup-window-content-white popup-window-with-titlebar popup-window-fixed-width',
    });
    oPopup.setContent(message);
    oPopup.show();
}