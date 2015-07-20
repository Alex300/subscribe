<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=module
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

// Environment setup
$env['location'] = 'subscribe';

// Self requirements (if needed)
require_once cot_incfile($env['ext'], 'module');

// Default controller
if(empty($m)) $m = 'main';

// Default ACL
list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth($env['ext'], 'a');
cot_block($usr['auth_read']);

$controllerName = $env['ext'].'_controller_'.ucfirst($m);

// Only if the file exists...
if (class_exists($controllerName)) {

    /* Create the controller */
    $controller = new $controllerName();

    // TODO кеширование
    /* Perform the Request task */
    $currentAction = $a.'Action';
    if (!$a && method_exists($controller, 'indexAction')){
        $outContent = $controller->indexAction();
    }elseif (method_exists($controller, $currentAction)){
        $outContent = $controller->$currentAction();
    }else{
        // Error page
        cot_die_message(404);
        exit;
    }

    if(!defined('CLI')) {
        require_once $cfg['system_dir'] . '/header.php';
        if (isset($outContent)) echo $outContent;
        require_once $cfg['system_dir'] . '/footer.php';
    } else {
        $content = $outContent;
    }
}else{
    // Error page
    cot_die_message(404);
    exit;
}