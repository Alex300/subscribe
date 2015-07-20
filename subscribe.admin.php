<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin
[END_COT_EXT]
==================== */

/**
 * Catalog admin panel
 *
 * @package Catalog
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

// Environment setup

// Self requirements
require_once cot_incfile($env['ext'], 'module');

// Default controller
if (!$n) $n = 'Main';

// Default ACL
list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth(cot::$env['ext'], 'a');
cot_block($usr['isadmin']);


$adminpath[] = array(cot_url('admin', 'm=extensions'), cot::$L['Extensions']);
$adminpath[] = array(cot_url('admin', 'm=extensions&a=details&mod='.$m), cot::$L['subscribe_subscribes']);
$adminpath[] = array(cot_url('admin', 'm='.$m), cot::$L['Administration']);
$adminhelp = '';


// TODO кеширование
$view = new View();

$controllerName = cot::$env['ext'].'_controller_Admin'.ucfirst($n);

// Only if the file exists...
if (class_exists($controllerName)) {

    /* Create the controller */
    $controller = new $controllerName();

    if(!$a) $a = cot_import('a', 'P', 'TXT');
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

}else{
    // Error page
    cot_die_message(404);
    exit;
}

if (COT_AJAX && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Не использовать эту фичу, если $_SERVER["REQUEST_METHOD"] == 'GET' т.к. это поломает ajax пагинацию
    require_once cot::$cfg['system_dir'] . '/header.php';
    echo $outContent;
    require_once cot::$cfg['system_dir'] . '/footer.php';
    exit;
}

$view->content = $outContent;

// Error and message handling
//cot_display_messages($t);

$adminsubtitle = cot::$L['subscribe_subscribe_title'];
$adminmain = $view->render(cot::$env['ext'].'.admin');
