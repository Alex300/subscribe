<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');


/**
 * Subscribe module for Cotonti Siena
 *     Queue admin controller
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 */
class subscribe_controller_AdminQueue
{

    /**
     * Список писем в очереди на отправку
     * @return string
     * @throws Exception
     */
    public function indexAction() {

        global $admintitle, $adminpath, $Ls;

        $admintitle  = cot::$L['subscribe_queue'];
        $adminpath[] = array(cot_url('admin', array('m'=>'subscribe', 'n'=>'queue')), $admintitle);

        $sort = cot_import('s', 'G', 'ALP');       // order field name
        $way = cot_import('w', 'G', 'ALP', 4);    // order way (asc, desc)
        $maxrowsperpage = cot::$cfg['maxrowsperpage'];

        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.queue.list.first') as $pl) {
            include $pl;
        }
        /* ===== */

        $sort = empty($sort) ? 'id' : $sort;
        $way = (empty($way) || !in_array($way, array('asc', 'desc'))) ? 'asc' : $way;

        $urlParams = array('m' => 'subscribe', 'n'=>'queue');
        if ($sort != 'id') $urlParams['s'] = $sort;
        if ($way  != 'asc')   $urlParams['w'] = $way;

        $where = array();

        // Фильтры
        $allowedFilters = array('sid', 'to_name', 'to_email');
        $f = cot_import('f', 'G', 'ARR');
        $filterForm = array('hidden' => '');
        if(!empty($f)) {
            foreach($f as $key => $val) {
                if(!in_array($key, $allowedFilters)) unset($f[$key]);
            }

            if(!empty($f['sid'])) {
                $where['sid'] = array('subscribe', $f['sid']);
                $urlParams['f[sid]'] = $f['sid'];
            }

            if(!empty($f['to_name'])) {
                $where['to_name'] = array('to_name', '*'.$f['to_name'].'*');
                $urlParams['f[to_name]'] = $f['to_name'];
            }

            if(!empty($f['to_email'])) {
                $where['to_email'] = array('to_email', '*'.$f['to_email'].'*');
                $urlParams['f[to_email]'] = $f['to_email'];
            }
        }
        if(isset(cot::$cfg['plugin']['urleditor']) && cot::$cfg['plugin']['urleditor']['preset'] != 'handy') {
            $filterForm['hidden'] .= cot_inputbox('hidden', 'm', 'subscribe');
        }
        $filterForm['hidden'] .= cot_inputbox('hidden', 'n', 'queue');

        $condition = array();
        foreach($where as $key => $val) {
            $condition[] = $val;
        }

        $order = array(array($sort, $way));

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.queue.list.query') as $pl) {
            include $pl;
        }
        /* ===== */
        $totallines = subscribe_model_Queue::count($condition);
        $items = null;
        if($totallines > 0) $items = subscribe_model_Queue::find($condition, $maxrowsperpage, $d, $order);

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
        foreach (cot_getextplugins('subscribe.admin.queue.list.main') as $pl) {
            include $pl;
        }
        /* ===== */

        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', true);
        if(empty($pagenav['current'])) $pagenav['current'] = 1;

        $pagenav['page'] = $pagenav['current'];
        if(!cot::$cfg['easypagenav']) $pagenav['page'] = ($pagenav['current'] - 1) * $maxrowsperpage;

        $subscribes = subscribe_model_Subscribe::keyValPairs();

        $template = array('subscribe', 'admin', 'queue', 'list');

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

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.admin.queue.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }


    public function deleteAction() {
        $id = cot_import('id', 'G', 'INT');
        $d = cot_import('d', 'G', 'INT');

        $backUrlParams = array('m'=>'subscribe', 'n'=>'queue');
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
            cot_error(cot::$L['nf']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item = subscribe_model_Queue::getById($id);
        if(!$item) {
            cot_error(cot::$L['nf']);
            cot_redirect(cot_url('admin', $backUrlParams));
        }

        $item->delete();

        cot_message(cot::$L['Deleted']);
        cot_redirect(cot_url('admin', $backUrlParams, '', true));
    }
}

