<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Subscribe module for Cotonti Siena
 *     Widget controller
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 */
class subscribe_controller_Widget
{
    /**
     * Форма подписки на рассылку
     * @param $id
     * @param string $tpl
     *
     * @return string HTML code
     */
    public static function form($id, $tpl = 'subscribe.widget.form') {

        $subscribe = subscribe_model_Subscribe::getById($id);

        if($subscribe) {
            Resources::linkFileFooter(cot::$cfg['modules_dir'].'/subscribe/js/subscriber.js');
        }

        $view = new View();
        $view->subscribe = $subscribe;

        if(empty($tpl)) {
            $tpl = array('subscribe', 'widget', 'form');
            if (!empty($subscribe) && !empty($subscribe->alias)) $tpl[] = $subscribe->alias;
        }

        return $view->render($tpl, 'module');
    }
}