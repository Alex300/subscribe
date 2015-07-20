<?php
defined('COT_CODE') or die('Wrong URL.');


/**
 * Subscribe module for Cotonti Siena
 *     User controller
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class subscribe_controller_User
{

    /**
     * Список рассылок
     *
     * @return string
     * @throws Exception
     */
    public function indexAction() {

        $uid = cot_import('uid', 'G', 'INT');
        if(!$uid || !cot::$usr['isadmin']) {
            $uid = cot::$usr['id'];
        }

        // Мои подписки - только для авторизованных пользователей
        if(!$uid) cot_die_message('404');

        $user = cot_user_data($uid);
        if(!$user) cot_die_message('404');

        cot::$out['canonical_uri'] = cot_url('subscribe', array('m'=>'user', 'uid'=>$uid));

        $urlParams = array('m'=>'user');
        if($uid != cot::$usr['id']) $urlParams['uid'] = $uid;

        $crumbs = array();
        if($uid != cot::$usr['id']) {
            cot::$out['subtitle'] = $title = cot::$L['subscribe_user_subscribes'].': '.cot_user_full_name($user);
            $crumbs[] = array(cot_url("users"), cot::$L['Users']);
            $crumbs[] = array(cot_url("users", "m=details&id=".$user["user_id"]."&u=".$user["user_name"] ),
                cot_user_full_name($user));
            $crumbs[] = cot::$L['subscribe_user_subscribes'];

            $urlParams['uid'] = $user['user_id'];

        } else {
            cot::$out['subtitle'] = $title = cot::$L['subscribe_my'];
            $crumbs[] = array(cot_url('users', array('m'=>'details')), cot::$L['subscribe_my_page']);
            $crumbs[] = cot::$L['subscribe_my'];
        }

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.user.list.query') as $pl) {
            include $pl;
        }
        /* ===== */

        // Все подписки данного пользователя
        $userSubscribes = subscribe_model_Subscriber::find(array(
            array('user', $uid),
            array('email', $user['user_email'], '=', 'OR')
        ), 0, 0, array(array('active', 'desc')));

        $subIds = array();
        $totallines = 0;
        if($userSubscribes) {
            $totallines = count($userSubscribes);
            foreach($userSubscribes as $subscriberRow) {
                $subIds[] = $subscriberRow->rawValue('subscribe');
            }
        }

        $subscribes = null;
        if(!empty($subIds)) {
            $subIds = array_unique($subIds);
            $subscribes = subscribe_model_Subscribe::find(array(array('id',$subIds)), 0, 0, array(array('title', 'asc')));

            if(!empty($subscribes)) {
                foreach($userSubscribes as $key => $subscriberRow) {
                    // маловероятная ситуация, но все же
                    if(!isset($subscribes[$subscriberRow->rawValue('subscribe')])) {
                        // Рассылки больше не существует.
                        // Удалить подписку
                        $subscriberRow->delete();
                        unset($userSubscribes[$key]);
                    }
                }
            }
        }

        Resources::linkFileFooter(cot::$cfg['modules_dir'].'/subscribe/js/subscriber.js');

        $breadcrumbs = '';
        if(!empty($crumbs)) $breadcrumbs = cot_breadcrumbs($crumbs, cot::$cfg['homebreadcrumb'], true);

        $template = array('subscribe', 'user', 'list');

        $view = new View();

        $view->breadcrumbs = $breadcrumbs;
        $view->page_title = htmlspecialchars($title);
        $view->fistNumber = 1;
        $view->userSubscribes = $userSubscribes;
        $view->subscribes = $subscribes;
        $view->totalitems = $totallines;
        $view->user = $user;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.user.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    /**
     * Обработка Ajax запроса на подписку на рассылку или отписку
     */
    public function ajxSubscribeToggleAction() {
        global $db_users;

        $ret = array('error' => '', 'message' => '');

        $id = cot_import('id', 'P', 'INT');
        $email = cot_import('email', 'P', 'TXT');

        $uid = cot_import('uid', 'P', 'INT');
        if(!$uid || !cot::$usr['isadmin']) {
            $uid = cot::$usr['id'];
        }

        // Только для авторизованных пользователей
        if(!$uid) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        // Управлять чужими подписками может только администратор
        if($uid != cot::$usr['id'] && !cot::$usr['isadmin']) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        $user = cot_user_data($uid);
        if(!$user || empty($user['user_email'])) {
            $ret['error'] = cot::$L['subscribe_err_user_not_found'];
            echo json_encode($ret);
            exit;
        }

        if(!$id) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        $subscribe = subscribe_model_Subscribe::getById($id);
        if(!$subscribe) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        $cond = array(
            array('SQL', "subscribe={$id} AND (user={$uid} OR email=".cot::$db->quote(cot::$usr['profile']['user_email']).')')
        );

        $subscriber = subscribe_model_Subscriber::fetchOne($cond);
        if(!$subscriber) {
            $subscriber = new subscribe_model_Subscriber();
            $subscriber->subscribe = $id;
        }

        $subscriber->email = $user['user_email'];
        $subscriber->user  = $uid;
        $subscriber->name = cot_user_full_name($user);

        if(!$subscriber->email_valid) {
            $subscriber->email_valid = 1;
            $subscriber->email_valid_date = cot_date('Y-m-d H:i:s', cot::$sys['now']);
        }

        if(!$subscriber->active) {
            // Включить
            // Проверка на то что рассылка включена
            if(!$subscribe->active) {
                $ret['error'] = cot::$L['subscribe_err_disabled'];
                echo json_encode($ret);
                exit;
            }

            // Если эта функция будет доступна незарегам...
            if($subscriber->email_valid) {
                $subscriber->active = 1;
                if($user['user_id'] == cot::$usr['id']) {
                    cot_message(sprintf(cot::$L['subscribe_msg_you_subscribed'], $subscribe->title));
                } else {
                    cot_message(cot::$L['Saved']);
                }
            }

        } else {
            $subscriber->active = 0;
            if($user['user_id'] == cot::$usr['id']) {
                cot_message(sprintf(cot::$L['subscribe_msg_you_unsubscribed'], $subscribe->title));
            } else {
                cot_message(cot::$L['Saved']);
            }
        }

        $subscriber->save();

        echo json_encode($ret);
        exit;
    }


    /**
     * Обработка Ajax запроса на подписку на рассылку
     * Используется виджетом
     * Принимает email пользователя
     * Допускается подписка неавторизованными пользователями (они должны подтвердить email)
     */
    public function ajxSubscribeAction() {
        global $db_users;

        $ret = array('error' => '', 'message' => '');

        $id = cot_import('id', 'P', 'INT');
        $email = cot_import('email', 'P', 'TXT');

        if(!$id) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        $subscribe = subscribe_model_Subscribe::getById($id);
        if(!$subscribe) {
            $ret['error'] = cot::$L['subscribe_err_not_found'];
            echo json_encode($ret);
            exit;
        }

        if(!$subscribe->active) {
            $ret['error'] = cot::$L['subscribe_err_disabled'];
            echo json_encode($ret);
            exit;
        }

        if(empty($email)) {
            $ret['error'] = cot::$L['field_required'].': '.cot::$L['Email'];
            echo json_encode($ret);
            exit;
        }

        $tmp = subscribe_checkEmail($email);
        if($tmp !== true) {
            $ret['error'] = $tmp;
            echo json_encode($ret);
            exit;
        }

        $email = mb_strtolower($email);

        $subscriber = subscribe_model_Subscriber::fetchOne(array(
            array('subscribe', $id),
            array('email', $email)

        ));
        if($subscriber) {
            if($subscriber->active) {
                $ret['error'] = sprintf(cot::$L['subscribe_err_user_subscribed'], $email, $subscribe->title);
                echo json_encode($ret);
                exit;
            }
        } else {
            $subscriber = new subscribe_model_Subscriber();
        }

        $sql = cot::$db->query("SELECT * FROM $db_users WHERE user_email = ? LIMIT 1", $email);
        $user = $sql->fetch();

        $subscriber->subscribe = $id;
        $subscriber->email = $email;

        if(!empty($user)) {
            $subscriber->user = $user['user_id'];
            $subscriber->name = cot_user_full_name($user);
        }

        $needConfirm = cot::$cfg['subscribe']['guestConfirmMail'];
        if(cot::$usr['id'] > 0) {
            if (cot::$usr['id'] == $user['user_id'] || cot::$usr['isadmin']) $needConfirm = false;
        }

        if($needConfirm) {
            // Возможно пользователь уже подтверждал свой email
            $tmp = subscribe_model_Subscriber::count(array(
                array('email', $email),
                array('email_valid', 1)
            ));
            if($tmp > 0) $needConfirm = false;
        }

        if(!$needConfirm) {
            $subscriber->email_valid = 1;
            $subscriber->email_valid_date = date('Y-m-d H:i:s', cot::$sys['now']);
            $subscriber->active = 1;
        }


        // Сохранение
        $subscriber->save();
        $ret['message'] = sprintf(cot::$L['subscribe_msg_you_subscribed'], $subscribe->title);

        // Письмо для поджтверждения e-mail адреса
        if($needConfirm) {
            $confirmUrl = cot_url('subscribe', array('m'=>'user', 'a'=>'confirm', 'code'=>$subscriber->unsubscr_code));
            if(!cot_url_check($confirmUrl)) $confirmUrl = cot::$cfg['mainurl'].'/'.$confirmUrl;

            $mailView = new View();
            $mailView->subscriber = $subscriber;
            $mailView->subscribe = $subscribe;
            $mailView->confirmUrl = $confirmUrl;

            $mailTpl = array('subscribe', 'mail_confirm', cot::$usr['lang']);

            $mailBody = $mailView->render($mailTpl);

            cot_mail($email, cot::$L['subscribe_confirm'], $mailBody, '', false, null, true);

            $ret['message'] .= cot::$L['subscribe_wait_confirm'];
        }

        echo json_encode($ret);
        exit;
    }

    /**
     * Подтверждение подписки на рассылку
     */
    public function confirmAction() {
        $code = cot_import('code', 'G', 'TXT');

        if(!$code) {
            cot_die_message('404');
        }

        $title = cot::$L['subscribe_confirm'];

        $subscriber = subscribe_model_Subscriber::fetchOne(array(array('unsubscr_code', $code)));
        if(!$subscriber) {
            cot_error(cot::$L['subscribe_err_wrong_confirm_code']);
        }

        cot::$sys['sublocation'] = $title;
        cot::$out['subtitle'] = $title;

        if($subscriber) {
            if(!$subscriber->subscribe->active) {
                cot_error(cot::$L['subscribe_err_disabled']);
            }

            $title .= ': '.$subscriber->subscribe->title;

            cot::$sys['sublocation'] = $title;
            cot::$out['subtitle'] = $title;

            $allItems = subscribe_model_Subscriber::find(array(
                array('email', $subscriber->email),
                array('email_valid', 0),
                array('id', $subscriber->id, '<>')
            ));
            if($allItems) {
                foreach($allItems as $itemRow) {
                    $itemRow->active = 1;
                    $itemRow->email_valid = 1;
                    $itemRow->email_valid_date = date('Y-m-d H:i:s', cot::$sys['now']);
                    $itemRow->save();
                }
                unset($allItems);
            }

            $subscriber->active = 1;
            $subscriber->email_valid = 1;
            $subscriber->email_valid_date = date('Y-m-d H:i:s', cot::$sys['now']);
            $subscriber->save();

            cot_message(sprintf(cot::$L['subscribe_confirmed'], $subscriber->email, $subscriber->subscribe->title));
        }

        $template = array('subscribe', 'confirm');

        $view = new View();

        $view->page_title = $title;
        $view->subscriber = $subscriber;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.confirm.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    /**
     * Отписаться от рассылки
     */
    public function unsubscribeAction() {
        $code = cot_import('code', 'G', 'TXT');

        if(!$code) {
            cot_die_message('404');
        }

        $title = cot::$L['subscribe_unsubscribe'];

        $subscriber = subscribe_model_Subscriber::fetchOne(array(array('unsubscr_code', $code)));
        if(!$subscriber) {
            cot_error(cot::$L['subscribe_err_wrong_unsubscribe_code']);
        }

        cot::$sys['sublocation'] = $title;
        cot::$out['subtitle'] = $title;

        if($subscriber) {
            $title .= ': '.$subscriber->subscribe->title;

            cot::$sys['sublocation'] = $title;
            cot::$out['subtitle'] = $title;
            $subscriber->active = 0;
            $subscriber->save();

            cot_message(sprintf(cot::$L['subscribe_msg_you_unsubscribed'], $subscriber->subscribe->title));
        }

        $template = array('subscribe', 'unsubscribe');

        $view = new View();

        $view->page_title = $title;
        $view->subscriber = $subscriber;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.unsubscribe.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }
}

