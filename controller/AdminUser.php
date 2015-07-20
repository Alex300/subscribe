<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');


/**
 * Subscribe module for Cotonti Siena
 *     User admin controller
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 */
class subscribe_controller_AdminUser
{

    /**
     * Список рассылок
     * @return string
     * @throws Exception
     */
    public function indexAction() {

        global $admintitle, $adminpath, $Ls;

        $admintitle  = cot::$L['subscribe_subscribers'];
        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'n'=>'user')), $admintitle);

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);    // order way (asc, desc)
        $maxrowsperpage = cot::$cfg['maxrowsperpage'];

        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.subscriber.list.first') as $pl) {
            include $pl;
        }
        /* ===== */

        $sort = empty($sort) ? 'email' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'asc' : $way;

        $urlParams = array('m' => 'subscribe', 'n'=>'user');
        if ($sort != 'email') $urlParams['s'] = $sort;
        if ($way  != 'asc')   $urlParams['w'] = $way;

        $where = array();

        $states = array(
            -1 => cot::$R['code_option_empty'],
            0 => cot::$L['Disabled'],
            1 => cot::$L['Enabled'],
        );

        // Фильтры
        $allowedFilters = array('id', 'sid', 'name', 'email', 'active');
        $f = cot_import('f', 'G', 'ARR');
        $filterForm = array('hidden' => '');
        if(!empty($f)) {
            foreach($f as $key => $val) {
                if(!in_array($key, $allowedFilters)) unset($f[$key]);
            }

            if(!empty($f['id'])) {
                $where['id'] = array('id', $f['id']);
                $urlParams['f[id]'] = $f['id'];
            }

            if(!empty($f['sid'])) {
                $where['sid'] = array('subscribe', $f['sid']);
                $urlParams['f[sid]'] = $f['sid'];
            }

            $f['active'] = isset($_GET['f']['active']) ? cot_import($_GET['f']['active'], 'D', 'INT') : -2;
            if($f['active'] == -1) {
                unset($where['active']);
            } elseif($f['active'] >= 0 && $f['active'] < 2) {
                $where['active'] = array('active', $f['active']);
                $urlParams['f[active]'] = $f['active'];
            }

            if(!empty($f['name'])) {
                $where['name'] = array('name', '*'.$f['name'].'*');
                $urlParams['f[name]'] = $f['name'];
            }

            if(!empty($f['email'])) {
                $where['email'] = array('email', '*'.$f['email'].'*');
                $urlParams['f[email]'] = $f['email'];
            }
        }
        if(isset(cot::$cfg['plugin']['urleditor']) && cot::$cfg['plugin']['urleditor']['preset'] != 'handy') {
            $filterForm['hidden'] .= cot_inputbox('hidden', 'm', 'subscribe');
        }
        $filterForm['hidden'] .= cot_inputbox('hidden', 'n', 'user');

        $condition = array();
        foreach($where as $key => $val) {
            $condition[] = $val;
        }

        $order = array(array($sort, $way));

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.subscriber.list.query') as $pl) {
            include $pl;
        }
        /* ===== */
        $totallines = subscribe_model_Subscriber::count($condition);
        $items = null;
        if($totallines > 0) $items = subscribe_model_Subscriber::find($condition, $maxrowsperpage, $d, $order);

        // Если передан номер страницы превышающий максимальный
        if(empty($items) && $totallines > 0 && $pg > 1) {
            $totalpages = ceil($totallines / $maxrowsperpage);
            $args = $urlParams;
            if($totalpages > 1) {
                if (cot::$cfg['easypagenav']) {
                    $args['d'] = $totalpages;
                } else {
                    $args['d'] = ($totalpages - 1) * $maxrowsperpage;
                }
            }
            cot_redirect(cot_url('admin', $args, '', true));
        }

        //$addNewUrl = cot_url('admin', array('m'=>'subscribe','a' => 'edit'));

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.subscriber.list.main') as $pl) {
            include $pl;
        }
        /* ===== */

        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', true);
        if(empty($pagenav['current'])) $pagenav['current'] = 1;

        $pagenav['page'] = $pagenav['current'];
        if(!cot::$cfg['easypagenav']) $pagenav['page'] = ($pagenav['current'] - 1) * $maxrowsperpage;

        $subscribes = subscribe_model_Subscribe::keyValPairs();

        Resources::linkFileFooter(cot::$cfg['modules_dir'].'/subscribe/js/admin.subscriber.js');

        $template = array('subscribe', 'admin', 'subscriber', 'list');

        $view = new View();

        $view->page_title = $admintitle;
        $view->fistNumber = $d + 1;
        $view->items = $items;
        $view->totalitems = $totallines;
        $view->pagenav = $pagenav;
        $view->subscribes = $subscribes;
        $view->urlParams = $urlParams;
        $view->filter = $f;
        $view->filterForm = $filterForm;
        $view->states = $states;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.subscriber.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    public function ajxEditAction() {
        global $db_users;

        $ret = array('error' => '');

        $id = cot_import('subrid', 'P', 'INT');
        unset($_POST['id'], $_POST['subrid']);

        if($id > 0) {
            $subscriber = subscribe_model_Subscriber::getById($id);
            if(!$subscriber) {
                $ret['error'] = cot::$L['subscribe_err_subscriber_not_found'];
                echo json_encode($ret);
                exit;
            }
        } else {
            $subscriber = new subscribe_model_Subscriber();
        }

        if(!empty($_POST['email'])) $_POST['email'] = mb_strtolower($_POST['email']);

        $subscriber->setData($_POST);

        $error = array();

        $subscr = null;
        $tmp = $subscriber->rawValue('subscribe');
        if(empty($tmp)) {
            $error[] = cot::$L['field_required'].': '.cot::$L['subscribe_subscribe'];

        } else {
            $subscr = subscribe_model_Subscribe::getById($subscriber->rawValue('subscribe'));
            if (!$subscr) {
                $error[] = cot::$L['subscribe_err_not_found'];

            }
        }

        $user = null;
        if(!empty($subscriber->user)) {
            $user = cot_user_data($subscriber->user);
            // Если получили данные пользователя, то e-mail всегда берем из профиля
            if(isset($user['user_email'])) $subscriber->email = mb_strtolower($user['user_email']);
        }

        if(empty($subscriber->email)) {
            $error[] = cot::$L['field_required'].': '.cot::$L['Email'];

        } else {
            $tmp = subscribe_checkEmail($subscriber->email);
            if($tmp !== true) $error[] = $tmp;

            if($subscriber->rawValue('subscribe') > 0) {
                if(!empty($subscr)) {
                    $cond = array(
                        array('email', $subscriber->email),
                        array('subscribe', $subscr->id)
                    );
                    if($subscriber->id > 0) $cond[] = array('id', $subscriber->id, '<>');

                    $cnt = subscribe_model_Subscriber::count($cond);
                    if ($cnt > 0) {
                        $error[] = sprintf(cot::$L['subscribe_err_user_subscribed'], $subscriber->email, htmlspecialchars($subscr->title));
                    }
                }
            }
        }

        if(!empty($error)) {
            $ret['error'] = implode('<br />', $error);
            echo json_encode($ret);
            exit;
        }


        if(empty($subscriber->name) && (!empty($subscriber->email) || !empty($user)) ) {
            if(!empty($user)) {
                $subscriber->name = cot_user_full_name($user);

            } else {
                $sql = cot::$db->query("SELECT * FROM $db_users WHERE user_email = ? LIMIT 1", $subscriber->email);
                $user = $sql->fetch();
                if(!empty($user)) {
                    $subscriber->name = cot_user_full_name($user);
                    if(empty($subscriber->user)) $subscriber->user = $user['user_id'];
                }
            }
        }

        // Админ при сохранении подтверждает e-mail
        if(!$subscriber->email_valid && (empty($subscriber->id) || $subscriber->active)) {
            $subscriber->email_valid = 1;
            $subscriber->email_valid_date = date('Y-m-d H:i:s', cot::$sys['now']);
        }

        // Сохранение
        $subscriber->save();
        cot_message(cot::$L['Saved']);

        echo json_encode($ret);
        exit;
    }


    public function ajxEnableAction() {
        $ret = array('error' => '');

        $id = cot_import('id', 'P', 'INT');

        if(empty($id)) {
            $ret['error'] = cot::$L['subscribe_err_subscriber_not_found'];
            echo json_encode($ret);
            exit;

        } else {
            $subscriber = subscribe_model_Subscriber::getById($id);
            if(!$subscriber) {
                $ret['error'] = cot::$L['subscribe_err_subscriber_not_found'];
                echo json_encode($ret);
                exit;
            }
        }

        if($subscriber->active == 1) {
            $subscriber->active = 0;

        } else {
            $subscriber->active = 1;
            if(!$subscriber->email_valid) {
                $subscriber->email_valid = 1;
                $subscriber->email_valid_date = date('Y-m-d H:i:s', cot::$sys['now']);
            }
        }

        // Сохранение
        $subscriber->save();
        cot_message(cot::$L['Saved']);

        echo json_encode($ret);
        exit;
    }

    public function deleteAction() {

        $id = cot_import('id', 'G', 'INT');
        $d = cot_import('d', 'G', 'INT');

        $backUrlParams = array('m'=>'subscribe', 'n'=>'user');
        if(!empty($d)) $backUrlParams['d'] = $d;

        // Фильтры из списка
        $f = cot_import('f', 'G', 'ARR');
        if(!empty($f)) {
            foreach ($f as $key => $val) {
                if($key == 'id') continue;
                $backUrlParams["f[{$key}]"] = $val;
            }
        }

        if(!$id) {
            cot_error(cot::$L['subscribe_err_subscriber_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item = subscribe_model_Subscriber::getById($id);
        if(!$item) {
            cot_error(cot::$L['subscribe_err_subscriber_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $email = $item->email;
        $item->delete();

        cot_message(sprintf(cot::$L['subscribe_subscriber_deleted'], $email));
        cot_redirect(cot_url('admin', $backUrlParams, '', true));
    }
}

