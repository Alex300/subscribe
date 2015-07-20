<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');


/**
 * Subscribe module for Cotonti Siena
 *     Main admin controller
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 */
class subscribe_controller_AdminMain
{

    /**
     * Список рассылок
     * @return string
     * @throws Exception
     */
    public function indexAction() {

        global $admintitle, $adminpath, $Ls;

        $admintitle  = cot::$L['subscribe_subscribes'];
        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe')), $admintitle);

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)
        $maxrowsperpage = cot::$cfg['maxrowsperpage'];

        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.list.first') as $pl) {
            include $pl;
        }
        /* ===== */

        $sort = empty($sort) ? 'title' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'asc' : $way;

        $urlParams = array('m' => 'subscribe');
        if ($sort != 'title') $urlParams['s'] = $sort;
        if ($way  != 'asc')   $urlParams['w'] = $way;

        $where = array();

        $states = array(
            -1 => cot::$R['code_option_empty'],
            0 => cot::$L['Disabled'],
            1 => cot::$L['Enabled'],
        );

        // Фильтры
        $allowedFilters = array('id', 'title', 'active');
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

            $f['active'] = isset($_GET['f']['active']) ? cot_import($_GET['f']['active'], 'D', 'INT') : -2;
            if($f['active'] == -1) {
                unset($where['active']);
            } elseif($f['active'] >= 0 && $f['active'] < 2) {
                $where['active'] = array('active', $f['active']);
                $urlParams['f[active]'] = $f['active'];
            }

            if(!empty($f['title'])) {
                $where['title'] = array('title', '*'.$f['title'].'*');
                $urlParams['f[title]'] = $f['title'];
            }
        }
        if(isset(cot::$cfg['plugin']['urleditor']) && cot::$cfg['plugin']['urleditor']['preset'] != 'handy') {
            $filterForm['hidden'] .= cot_inputbox('hidden', 'm', 'brs');
        }

        $condition = array();
        foreach($where as $key => $val) {
            $condition[] = $val;
        }

        $order = array(array($sort, $way));

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.list.query') as $pl) {
            include $pl;
        }
        /* ===== */
        $totallines = subscribe_model_Subscribe::count($condition);
        $items = null;
        if($totallines > 0) $items = subscribe_model_Subscribe::find($condition, $maxrowsperpage, $d, $order);

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

        $addNewUrl = cot_url('admin', array('m'=>'subscribe','a' => 'edit'));

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.list.main') as $pl) {
            include $pl;
        }
        /* ===== */

        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', true);
        if(empty($pagenav['current'])) $pagenav['current'] = 1;

        $pagenav['page'] = $pagenav['current'];
        if(!cot::$cfg['easypagenav']) $pagenav['page'] = ($pagenav['current'] - 1) * $maxrowsperpage;

        $template = array('subscribe', 'admin', 'list');

        $view = new View();

        $view->page_title = $admintitle;
        $view->fistNumber = $d + 1;
        $view->items = $items;
        $view->totalitems = $totallines;
        $view->filterForm = $filterForm;
        $view->pagenav = $pagenav;
        $view->addNewUrl = $addNewUrl;
        $view->urlParams = $urlParams;
        $view->filter = $f;
        $view->states = $states;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }


    public function viewAction(){
        global $admintitle, $adminpath, $Ls;

        $id = cot_import('id', 'G', 'INT');           // id Рассылки
        if(empty($id)) {
            cot_error(cot::$L['subscribe_err_not_found']);
            cot_redirect(cot_url('admin', array('m'=>'subscribe'), '', true));
        }

        $subscribe = subscribe_model_Subscribe::getById($id);
        if(empty($subscribe)) {
            cot_error(cot::$L['subscribe_err_not_found']);
            cot_redirect(cot_url('admin', array('m'=>'subscribe'), '', true));
        }

        $admintitle  = $subscribe->title;

        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe')), cot::$L['subscribe_subscribes']);
        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'a'=>'edit', 'id' => $id)), $subscribe->title);
        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'a'=>'view', 'id' => $id)), cot::$L['Preview']);

        $rpc = trim($subscribe->content_url);
        if ($rpc != '') {
            $text = file_get_contents($rpc);
            if (!$text || $text == '') {
                echo "[Error] I can not get email content from address: '{$rpc}' \n";
                echo "Stopped\n\n";
                ob_flush();
                return false;
            }

        } else {
            $text = $subscribe->text;

            preg_match_all("/\[URL#([a-zA-Z0-9-\?_\=\:\/\.\&;,]+)#]/ies",$text,$m);
            $_vars = array();
            foreach ($m[1] as $varName){
                $varurl = html_entity_decode($varName);
                $_vars["[URL#" . $varName . "#]"] = file_get_contents($varurl);
            }
            $text = str_replace(array_keys($_vars), $_vars, $text);
            $text = str_replace('[YEAR]', cot_date('Y'), $text);

        }

        $msgView = new View();
        $msgView->subscribe = $subscribe;
        $msgView->text = $text;

        $tpl = array('subscribe','mail');
        if(!empty($subscribe->alias)) $tpl[] = $subscribe->alias;

        $fromTitle = cot::$cfg['maintitle'];
        if (!empty($subscribe->from_title)) $fromTitle = $subscribe->from_title;

        $fromEmail = cot::$cfg["adminemail"];
        if (!empty($subscribe->from_mail)) $fromEmail = $subscribe->from_mail;
        $subject = (!empty($subscribe->subject)) ? $subscribe->subject : $subscribe->title;

        $data = array(
            'subscribe' => $subscribe->id,
            'subject' => $subject,
            'fromName' => $fromTitle,
            'fromEmail' => $fromEmail,
        );

        // Тестовый подписчик
        $item = array(
            'id' => 123,
            'email' => 'qwe@qwe.com',
            'name'  => 'Test User',
            'unsubscr_code' => '123456789qwertyu',
        );

        $msgView->subscribe = $subscribe;
        $msgView->subscriber = $item;
        $msgBody = $msgView->render($tpl);

        $data['subscriber'] = $item['id'];
        $data['toEmail'] = $item['email'];
        $data['toName'] = $item['name'];

        // Поддержка тегов 'отписаться'
        $tmp = cot_url('subscribe', array('m'=>'user', 'a'=>'unsubscribe', 'code'=>$item['unsubscr_code']));
        if(!cot_url_check($tmp)) $tmp = cot::$cfg['mainurl'].'/'.$tmp;

        $toSend = str_replace('[UNSUBSCRIBE_URL]', "<a href=\"{$tmp}\">{$tmp}</a>", $msgBody);
        $data['text'] = $toSend;

        $subscribers = subscribe_model_Subscriber::count(array(array('subscribe', $id)));
        $activeSubscribers = subscribe_model_Subscriber::count(array(
            array('subscribe', $id),
            array('active', 1)
        ));

        $template = array('subscribe', 'admin', 'view');

        $view = new View();
        $view->page_title = $subscribe->title.' ['.cot::$L['Preview'].']';
        $view->subscribe = $subscribe;
        $view->subscriber = $item;
        $view->subscribers = $subscribers;
        $view->activeSubscribers = $activeSubscribers;
        $view->data = $data;

        return $view->render($template);
    }

    /**
     * Редактирование рассылки
     * @return string
     * @throws Exception
     */
    public function editAction() {
        global $cot_extrafields, $admintitle, $adminpath;

        $id = cot_import('id', 'G', 'INT');           // id Рассылки
        $act =  cot_import('act', 'G', 'ALP');
        if(empty($act))  $act =  cot_import('act', 'P', 'ALP');

        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe')), cot::$L['subscribe_subscribes']);

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.edit.first') as $pl)  {
            include $pl;
        }
        /* ===== */

        if(!$id){
            $item = new subscribe_model_Subscribe();
            $admintitle  = cot::$L['subscribe_add_new'];
            $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'a'=>'edit')), $admintitle);

        }else{
            $item = subscribe_model_Subscribe::getById($id);
            if(!$item) {
                cot_error(cot::$L['subscribe_err_not_found']);
                cot_redirect(cot_url('admin', array('m'=>'subscribe'), '', true));
            }

            if($act == 'clone') {
                $id = null;
                $item = clone $item;

                $admintitle  = cot::$L['subscribe_add_new'];
                $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'a'=>'edit')), $admintitle);

            } else {
                $admintitle  = $item->title." [".cot::$L['Edit']."]";
                $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'a'=>'edit','id' => $item->id)), $admintitle);
            }

        }

        // Сохранение
        if($act == 'save') {
            unset($_POST['id'], $_POST['user'], $_POST['x'], $_POST['act']);

            /* === Hook === */
            foreach (cot_getextplugins('subscribe.admin.save.first') as $pl) {
                include $pl;
            }
            /* ===== */

            $data = $_POST;
            $data['next_run'] = cot_import_date('next_run');
            if(!empty($data['next_run'])) $data['next_run'] = date('Y-m-d H:i:s', $data['next_run']);

            $item->setData($data);

            /* === Hook === */
            foreach (cot_getextplugins('subscribe.admin.save.validate') as $pl) {
                include $pl;
            }
            /* ===== */

            // There is some errors
            if(!$item->validate() || cot_error_found()) {
                $urlParams = array(
                    'm' => 'subscribe',
                    'a' => 'edit'
                );
                if($item->id > 0) $urlParams['id'] = $item->id;
                cot_redirect(cot_url('admin', $urlParams, '', true));
            }

            $isNew = ($item->id == 0);

            // Перерасчет времени следующего запуска
            // Делать это в админке при редактировании рассылки и при выполнении рассылки
            // А то могут быть коллизии
            $recalculate = true;
            if(!empty($item->next_run)) {
                $tmp = strtotime($item->next_run);
                if($tmp > cot::$sys['now']) $recalculate = false;
            }
            if($recalculate) $item->next_run = $item->getNextRunDate();

            // Сохранение
            if($item->save()) {
                cot_message(cot::$L['Saved']);

                $urlParams = array(
                    'm' => 'subscribe',
                    'a' => 'edit',
                    'id' => $item->id
                );
                $redirectUrl = cot_url('admin', $urlParams, '', true);

                /* === Hook === */
                foreach (cot_getextplugins('subscribe.admin.save.done') as $pl) {
                    include $pl;
                }
                /* ===== */

                // Редирект на станицу рассылки
                cot_redirect($redirectUrl);
            }
        }

        // 'input_textarea_editor', 'input_textarea_medieditor', 'input_textarea_minieditor', ''
        $editor = 'input_textarea_editor';

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.edit.main') as $pl) {
            include $pl;
        }
        /* ===== */

        $nextRun = 0;
        if(!empty($item->next_run)) $nextRun = strtotime($item->next_run);

        $formElements = array(
            'hidden' => array(
                'element' => cot_inputbox('hidden', 'act', 'save')
            ),
            'title' => array(
                'element' => cot_inputbox('text', 'title', $item->rawValue('title')),
                'required' => true,
                'label' => subscribe_model_Subscribe::fieldLabel('title')
            ),
            'alias' => array(
                'element' => cot_inputbox('text', 'alias', $item->rawValue('alias')),
                'label' => subscribe_model_Subscribe::fieldLabel('alias')
            ),
            'admin_note' => array(
                'element' =>  cot_textarea('admin_note', $item->rawValue('admin_note'), 5, 120, ''),
                'label' => subscribe_model_Subscribe::fieldLabel('admin_note')
            ),
            'from_mail' => array(
                'element' => cot_inputbox('text', 'from_mail', $item->rawValue('from_mail')),
                'label' => subscribe_model_Subscribe::fieldLabel('from_mail'),
                'hint' => cot::$L['subscribe_from_mail_hint']
            ),
            'from_title' => array(
                'element' => cot_inputbox('text', 'from_title', $item->rawValue('from_title')),
                'label' => subscribe_model_Subscribe::fieldLabel('from_title')
            ),
            'subject' => array(
                'element' => cot_inputbox('text', 'subject', $item->rawValue('subject')),
                'label' => subscribe_model_Subscribe::fieldLabel('subject')
            ),
            'description' => array(
                'element' =>  cot_textarea('description', $item->rawValue('description'), 5, 120, '', $editor),
                'label' => subscribe_model_Subscribe::fieldLabel('description')
            ),
            'content_url' => array(
                'element' => cot_inputbox('text', 'content_url', $item->rawValue('content_url')),
                'label' => subscribe_model_Subscribe::fieldLabel('content_url'),
                'hint' => cot::$L['subscribe_content_url_hint']
            ),
            'text' => array(
                'element' => cot_textarea('text', $item->rawValue('text'), 5, 120, '', $editor),
                'label' => subscribe_model_Subscribe::fieldLabel('text'),
                'hint' => cot::$L['subscribe_text_hint']
            ),
            'next_run' => array(
                'element' => cot_selectbox_date($nextRun, 'long', 'next_run'),
                'label' => subscribe_model_Subscribe::fieldLabel('next_run'),
                'hint' => cot::$L['subscribe_next_run_hint']." ".cot::$usr['timetext']
            ),
            'sched_mday' => array(
                'element' => cot_inputbox('text', 'sched_mday', $item->rawValue('sched_mday')),
                'label' => subscribe_model_Subscribe::fieldLabel('sched_mday'),
                'hint' => cot::$L['subscribe_sched_mday_hint']
            ),
            'sched_wday' => array(
                'element' => cot_inputbox('text', 'sched_wday', $item->rawValue('sched_wday')),
                'label' => subscribe_model_Subscribe::fieldLabel('sched_wday'),
                'hint' => cot::$L['subscribe_sched_wday_hint']
            ),
            'sched_time' => array(
                'element' => cot_inputbox('text', 'sched_time', $item->rawValue('sched_time')),
                'label' => subscribe_model_Subscribe::fieldLabel('sched_time'),
                'hint' => cot::$L['subscribe_sched_time_hint']
            ),
            'active' => array(
                'element' => cot_checkbox($item->rawValue('active'), 'active', subscribe_model_Subscribe::fieldLabel('active')),
            ),
            'periodical' => array(
                'element' => cot_checkbox($item->rawValue('periodical'), 'periodical', subscribe_model_Subscribe::fieldLabel('periodical')),
            ),
            'sort' => array(
                'element' => cot_inputbox('text', 'sort', $item->rawValue('sort')),
                'label' => subscribe_model_Subscribe::fieldLabel('sort'),
            ),
        );
        if(!empty($cot_extrafields[cot::$db->subscribe])) {
            // Extra fields for subscribe
            foreach ($cot_extrafields[cot::$db->subscribe] as $exfld) {
                $fName = $exfld['field_name'];
                $formElements[$fName] = array(
                    'element' => cot_build_extrafields($fName, $exfld, $item->rawValue($fName)),
                );
                if($exfld['field_type'] !== 'checkbox') {
                    isset(cot::$L['subscribe_'.$exfld['field_name'].'_title']) ?
                        cot::$L['subscribe_'.$exfld['field_name'].'_title'] : subscribe_model_Subscribe::fieldLabel($fName);
                }
            }
        }

        $subscribers = subscribe_model_Subscriber::count(array(array('subscribe', $item->id)));
        $activeSubscribers = subscribe_model_Subscriber::count(array(
            array('subscribe', $item->id),
            array('active', 1)
        ));

        $actionParams = array(
            'm' => 'subscribe',
            'a' => 'edit'
        );
        if($item->id > 0) $actionParams['id'] = $item->id;

        $template = array('subscribe', 'admin', 'edit');

        $view = new View();

        $view->page_title = $admintitle;
        $view->item = $item;
        $view->subscribers = $subscribers;
        $view->activeSubscribers = $activeSubscribers;
        $view->formElements = $formElements;
        $view->formAction = cot_url('admin', $actionParams);

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.edit.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    public function deleteAction() {

        $id = cot_import('id', 'G', 'INT');
        $d = cot_import('d', 'G', 'INT');

        $backUrlParams = array('m'=>'subscribe');
        if(!empty($d)) $backUrlParams['d'] = $d;

        // Фильтры из списка
        $f = cot_import('f', 'G', 'ARR');
        if(!empty($f)) {
            foreach ($f as $key => $val) {
                if($key == 'id') continue;
                $backUrlParams["f[{$key}]"] = $val;
            }
        }

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way  = cot_import('w', 'G', 'ALP', 4);     // order way (asc, desc)
        if ($sort != 'title') $backUrlParams['s'] = $sort;
        if ($way  != 'asc')   $backUrlParams['w'] = $way;

        if(!$id) {
            cot_error(cot::$L['subscribe_err_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item = subscribe_model_Subscribe::getById($id);
        if(!$item) {
            cot_error(cot::$L['subscribe_err_not_found']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $title = $item->title;
        $item->delete();

        cot_message(sprintf(cot::$L['subscribe_deleted'], $title));
        cot_redirect(cot_url('admin', $backUrlParams, '', true));
    }
}

