<?

include_once "include/events/dimensions.php";
include_once "include/events/bizproc_popup.php";
include_once "include/events/order.php";
include_once "include/events/stages_control.php";
// include_once "include/events/webform_file.php";
// include_once "include/events/nomenclature_type.php";
include_once "include/iblock_props/form.php";
require_once 'include/for_bp/order.php';
require_once 'include/for_bp/create_deal.php';
define('TSV_LOG_ENABLED',true);
// $GLOBALS['MAX_SMART_FILTER']['PROPERTY_NOMENCLATURE_TYPE'] = 'Товар';
// $GLOBALS['arrFilterProp']['PROPERTY_NOMENCLATURE_TYPE'] = 'Товар';

// require_once 'include/mail/custom_mail.php';
require_once 'include/pushpull/function.php';

if(SITE_ID == 's2') {
    require_once 'include/interface/addorderbutton.php';
}