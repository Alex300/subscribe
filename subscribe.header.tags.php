<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=header.tags
[END_COT_EXT]
==================== */
/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

if (!COT_AJAX && defined('COT_ADMIN') && $cfg['admintheme'] == 'cpanel'){

    $admin_MenuUser['subscribe'] = array(
        'title' => cot::$L['subscribe_my'],
        'url' => cot_url('subscribe', array('m'=>'user')),
        'icon_class' => 'fa fa-envelope',
    );
}
