<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin.extrafields.first
[END_COT_EXT]
==================== */

/**
 * Ads board module for Cotonti Siena
 *
 * @package Advert
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('subscribe', 'module');

$extra_whitelist[cot::$db->subscribe] = array(
	'name' => cot::$db->subscribe,
	'caption' => cot::$L['Module'].' '.cot::$L['subscribe_subscribe_title'],
	'type' => 'module',
	'code' => 'subscribe',  // Extension code
	'tags' => array()
);

$extra_whitelist[cot::$db->subscriber] = array(
    'name' => cot::$db->subscriber,
    'caption' => cot::$L['Module'].' '.cot::$L['subscribe_subscribe_title'].' - '.cot::$L['subscribe_subscribers'],
    'type' => 'module',
    'code' => 'subscribe',
    'tags' => array()
);