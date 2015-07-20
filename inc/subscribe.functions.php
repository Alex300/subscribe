<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

cot::$db->registerTable('subscribe');
cot_extrafields_register_table('subscribe');

cot::$db->registerTable('subscriber');
cot_extrafields_register_table('subscriber');

cot::$db->registerTable('subscribe_queue');

// Requirements
require_once cot_langfile('subscribe', 'module');
//require_once  cot_incfile('subscribe', 'module', 'resources');

/**
 * Проверяем e-mail
 * @param string $mail - проверяемый e-mail
 *
 * @return bool|string TRUE or Error message
 */
function subscribe_checkEmail($mail = ''){
    global $db_banlist, $db;

    // Проверяем бан-лист
    if (cot_plugin_active('banlist')){
        $sql = cot::$db->query("SELECT banlist_reason, banlist_email FROM $db_banlist
            WHERE banlist_email LIKE ".cot::$db->quote('%'.$mail.'%'));
        if ($row = $sql->fetch()) {
            $ret = cot::$L['aut_emailbanned']. $row['banlist_reason'];
            return $ret;
        }
        $sql->closeCursor();
    }

    if(!cot_check_email($mail)){
        $ret = cot::$L['subscribe_err_wrongmail'];
        return $ret;
    }

    return true;
}


/**
 * Форма подписки на рассылку
 * CoTemplate callback
 *
 * @param $id
 * @param string $tpl
 *
 * @return string HTML code
 */
function subscribeForm($id, $tpl = 'subscribe.widget.form') {
    return subscribe_controller_Widget::form($id, $tpl);
}