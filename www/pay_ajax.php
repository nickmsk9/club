<?
require_once('pay_conf.php');
if (isset($_GET['prepare_once'])) {
    $hash = md5($fk_merchant_id.":".$_GET['oa'].":".$fk_merchant_key.":".$_GET['l']);
    echo $hash;
    exit;
}
?>