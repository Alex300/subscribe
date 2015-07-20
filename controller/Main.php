<?php
defined('COT_CODE') or die('Wrong URL.');


/**
 * Subscribe module for Cotonti Siena
 *     Main controller class
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class subscribe_controller_Main
{
    /**
     * Список рассылок
     */
    public function indexAction() {

        $maxrowsperpage = cot::$cfg['maxrowsperpage'];
        if($maxrowsperpage < 1) $maxrowsperpage = 1;

        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list

        $sort = 'title';
        $way = 'asc';

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.list.first') as $pl) {
            include $pl;
        }
        /* ===== */

        $urlParams = array();
        $canonicalUrlParams = array();
        if ($durl > 1)  $canonicalUrlParams['d'] = $durl;

        $where = array();

        cot::$out['subtitle'] = $title = cot::$L['subscribe_subscribes'];

        // Building the canonical URL
        cot::$out['canonical_uri'] = cot_url('subscribe', $canonicalUrlParams);

        $condition = array(
            array('active', 1),
            array('periodical', 1),
        );
        $order = array(array('active', 'desc'), array($sort, $way));

        $userSubscribesCondition = array();
        if(cot::$usr['id'] > 0) {
            $userSubscribesCondition[] = array('active', 1);
            $userSubscribesCondition[] = array('user', cot::$usr['id']);
            if(!empty(cot::$usr['profile']['user_email'])) {
                //$userSubscribesCondition[] = array('email', cot::$usr['profile']['user_email'], '=', 'OR');
                $userSubscribesCondition = array(
                    array('SQL', 'active=1 AND (user='.cot::$usr['id'].' OR email='.cot::$db->quote(cot::$usr['profile']['user_email']).')')
                );
            }
        }


        /* === Hook === */
        foreach (cot_getextplugins('subscribe.list.query') as $pl) {
            include $pl;
        }
        /* ===== */

        $totallines = subscribe_model_Subscribe::count($condition);
        $items = null;
        if($totallines > 0) $items = subscribe_model_Subscribe::find($condition, $maxrowsperpage, $d, $order);

        // Подписки пользователя
        $userSubscribes = null;
        $userSubscribesIds = array();
        if(!empty($items)) {
            if (cot::$usr['id'] > 0) {
                $userSubscribes = subscribe_model_Subscriber::find($userSubscribesCondition, 0, 0, array(array('active', 'desc')));
                if (!empty($userSubscribes)) {
                    foreach ($userSubscribes as $usRow) {
                        $userSubscribesIds[] = $usRow->rawValue('subscribe');
                    }
                }
            }
        }

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.list.main') as $pl) {
            include $pl;
        }
        /* ===== */

        if (cot::$usr['id'] > 0) Resources::linkFileFooter(cot::$cfg['modules_dir'].'/subscribe/js/subscriber.js');

        $crumbs = array(cot::$L['subscribe_subscribes']);

        $pagenav = cot_pagenav('subscribe', $urlParams, $d, $totallines, $maxrowsperpage);
        if(empty($pagenav['current'])) $pagenav['current'] = 1;

        $breadcrumbs = '';
        if(!empty($crumbs)) $breadcrumbs = cot_breadcrumbs($crumbs, cot::$cfg['homebreadcrumb'], true);

        $template = array('subscribe', 'list');
//        $pageUrlParams = $urlParams;
//        if($durl > 1) $pageUrlParams['d'] = $durl;

        $view = new View();
        $view->breadcrumbs = $breadcrumbs;
        $view->page_title = htmlspecialchars($title);
        $view->items = $items;
        $view->userSubscribes = $userSubscribes;
        $view->userSubscribesIds = $userSubscribesIds;
        $view->totalitems = $totallines;
        $view->pagenav = $pagenav;
        $view->urlParams = $urlParams;
//        $view->pageUrlParams = $pageUrlParams;

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.list.view') as $pl) {
            include $pl;
        }
        /* ===== */

        return $view->render($template);
    }

    /**
     * Запуск текущих рассылок
     * php cli.php --a subscribe.main.run > subscribe.log
     */
    public function runAction() {

        echo "---------------------------------------------------------------------------\n";

        // Проверка рассылок, выполнение которых могло завершиться ошибкой
        $condition = array(
            array('next_run', date('Y-m-d H:i:s', cot::$sys['now']), '<='),
            array('active', 1),
            array('periodical', 1),
            array('running', 1)
        );
        $items = subscribe_model_Subscribe::find($condition);

        if(!empty($items)) {
            foreach($items as $itemRow) {
                $pingTime = 0;
                if(!empty($itemRow->ping)) $pingTime = strtotime($itemRow->ping);

                // "Запущенная" когда-то рассылка уже ничего не делает более получаса. Вероятно она отвалилась
                if (!$pingTime || (cot::$sys['now'] - $pingTime) > 1800) {
                    $itemRow->ping = '1970-01-01 00:00:00';
                    $itemRow->running = 0;
                    // Считаем следующий запуск
                    $itemRow->next_run = $itemRow->getNextRunDate();
                    $itemRow->save();
                    unset($itemRow);
                    exit;
                }
            }
            unset($items);
        }

        // Получаем список рассылок:
        $condition = array(
            array('next_run', date('Y-m-d H:i:s', cot::$sys['now']), '<='),
            array('SQL', 'last_executed < next_run'),
            array('active', 1),
            array('running', 0)
        );
        $items = subscribe_model_Subscribe::find($condition);
        if (!$items) {
            echo "There are no scheduled mailings at this time\n";
            ob_flush();
            exit();
        }

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.run.main') as $pl) {
            include $pl;
        }
        /* ===== */

        /* === Hook - Part1 : Set === */
        $extp = cot_getextplugins('subscribe.run.loop');
        /* ===== */

        foreach ($items as $itemRow) {
            echo "Running: ".htmlspecialchars(strip_tags($itemRow->title))."\n";
            ob_flush();

            $lastSent = 0;
            $execute = true;

            /* === Hook - Part2 : Include === */
            foreach ($extp as $pl) {
                include $pl;
            }
            /* ===== */

            if($execute) {
                // Установить флаг запущенной рассылки
                $itemRow->running = 1;
                $itemRow->save();

                // Запустить рассылку
                $customFunc = '';
                if (!empty($itemRow->alias)) $customFunc = 'subscribe_run_' . $itemRow->alias;
                if (!empty($customFunc) && function_exists($customFunc)) {
                    $lastSent = $customFunc($itemRow);
                } else {
                    $lastSent = static::subscribeRun($itemRow);
                }
            }

            // Сбросить флаг запущенной рассылки
            $itemRow->running = 0;
            $itemRow->last_sent = $lastSent;
            if($lastSent !== false) {
                // Время последнего выполнения в случае успешного выполнения
                $itemRow->last_executed = date('Y-m-d H:i:s');

                if ($itemRow->periodical) {
                    $itemRow->next_run = $itemRow->getNextRunDate();

                } else {
                    // Если рассылка не периодическая, после выполнения, отключить ее
                    $itemRow->active = 0;
                }
            }

            if ($lastSent > 0) {


            } elseif ($lastSent === false) {
                echo " - [ERROR] An error occurred during execution\n";
            } else {
                echo " - No letter sent. Error or no subscribers\n";
            }
            $itemRow->save();
        }

        return '';
    }

    /**
     * @param subscribe_model_Subscribe $subscribe
     * @return int количество отосланных писем
     */
    public static function subscribeRun($subscribe) {

        if(empty($subscribe)) return false;

        $nowdate = date('Y-m-d H:i:s', cot::$sys['now']);

        // Сэкономим память. Ощутимо на большом количестве подписчиков
        $stmtSubscribers = cot::$db->query("SELECT * FROM ".subscribe_model_Subscriber::tableName()."
            WHERE subscribe={$subscribe->id} AND last_executed<'{$nowdate}' AND active=1");

        $count = $stmtSubscribers->rowCount();
        if($count == 0) return 0;

        $i = 0;
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
        echo "Subscribers count: ".$stmtSubscribers->rowCount()."\n";

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

        if(cot::$cfg['subscribe']['useQueue']) {
            $sender = new subscribe_sender_Queue();
        } else {
            $sender = new subscribe_sender_Cotmail();
        }

        $errors = 0;

        $data = array(
            'subscribe' => $subscribe->id,
            'subject' => $subject,
            'fromName' => $fromTitle,
            'fromEmail' => $fromEmail,
        );

        while ($item = $stmtSubscribers->fetch()) {
            echo " - Processing: ".trim($item['name'].' '.$item['email'])." ...";
            ob_flush();

            // Кешируем время выполнения
            // Memcache было бы лучше
            $subscribe->ping = date('Y-m-d H:i:s');   // Текущее системное время, а не то что было на момент старта скрипта
            $subscribe->save();

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
            $data['body'] = $toSend;

            $isError = false;
            try {
                $sender->send($data);

            } catch (Exception $e) {
                $isError = true;
                $errors ++;
                $errCode = $e->getCode();
                $errMsg = $e->getMessage();
                echo "\n[ERROR] {$errCode}:  {$errMsg}! While sending to: {$item['email']}\n";
                ob_flush();

                // Зафиксировать в базе ошибку отправки сообщения
                $tmpData = array(
                    'last_error' => mb_substr("{$errCode}:  {$errMsg}", 0, 253),
                );
                cot::$db->update(subscribe_model_Subscriber::tableName(), $tmpData, "id={$item['id']}");

                // TODO проанализировать ошибку, если User not found - отписать его, чтобы больше не слать ему письма
                // 500 - User not found
                // 553 - Invalid domain name
//                if ($errCode == 550 || $errCode == 553){
//                    // Зафиксировать в базе ошибку отправки сообщения
//                    echo "Отписываю его...\n";
//                    ob_flush();
//                    $data = array(
//                        'subr_on' => 'false',
//                        'subr_last_error' => "{$errCode}:  {$errMsg}",
//                    );
//                    $db->update('subscriber', $data, "subr_id={$item['subr_id']}");
//
//                }else{
                    var_dump($e);
                    echo "\n------------\n";
                    ob_flush();
//                }

            }

            if (!$isError) {
                echo " done\n";
                $tmpData = array(
                    'last_executed' => $nowdate,
                    'last_error' => ''
                );

                cot::$db->update(subscribe_model_Subscriber::tableName(), $tmpData, "id={$item['id']}");
                $i++;
            }

            unset($item);
            if($i % 100 == 0) gc_collect_cycles();
        }

        if ($errors > 0){
            echo "\nErrors count: $errors \n";
            ob_flush();
        }


        return $i;
    }


    /**
     * Запуск обработки очереди
     * php cli.php --a subscribe.main.runQueue > subscribeQueue.log
     */
    public function runQueueAction() {

        echo "---------------------------------------------------------------------------\n";

        // Проверка процессов, выполнение которых могло завершиться ошибкой
        $pidFile = cot::$cfg['modules_dir'].'/subscribe/inc/queue.txt';
        if(!file_exists($pidFile)) file_put_contents($pidFile, '0');

        if(!is_writeable($pidFile)) {
            echo "[ERROR] Can't set blocking. File '{$pidFile}' is not writable\n";
            ob_flush();
            exit();
        }

        $pingTime = intval(file_get_contents($pidFile));
        if ($pingTime > 0 && (cot::$sys['now'] - $pingTime) < 300) { // 5 минут
            // Выполняется другой процесс
            echo "Other sending process is running\n";
            ob_flush();
            exit();
        }

        // Получаем список писем:
        $condition = array();
        $limit = (int)cot::$cfg['subscribe']['queueCount'];
//        $items = subscribe_model_Queue::find($condition, $limit, 0, array(array('id', 'asc')));

        // Сэкономим память. Ощутимо на большом количестве подписчиков
        if($limit > 0) $limit = "LIMIT {$limit}";
        $stmtItems = cot::$db->query("SELECT * FROM ".subscribe_model_Queue::tableName()." ORDER BY `id` ASC $limit");

        $count = $stmtItems->rowCount();
        if (!$count) {
            echo "There are no emails to send at this time\n";
            ob_flush();
            exit();
        }

        echo "Emails count: ".$count."\n";

        // Реальная отправка писем
        $sender = new subscribe_sender_Cotmail();

        /* === Hook === */
        foreach (cot_getextplugins('subscribe.queue.run.main') as $pl) {
            include $pl;
        }
        /* ===== */

        /* === Hook - Part1 : Set === */
        $extp = cot_getextplugins('subscribe.queue.run.loop');
        /* ===== */

        $errors = 0;
        $i = 0;
        while ($item = $stmtItems->fetch()) {
            echo " - Processing: ".trim($item['name'].' '.$item['email'])." ...";
            ob_flush();

            $execute = true;

            /* === Hook - Part2 : Include === */
            foreach ($extp as $pl) {
                include $pl;
            }
            /* ===== */

            // Блокировка
            // Текущее системное время, а не то что было на момент старта скрипта
            file_put_contents($pidFile, time());

            if($execute) {
                $data = array(
                    'subscribe' => $item['subscribe'],
                    'subscriber'=> $item['subscriber'],
                    'subject'   => $item['subject'],
                    'fromName'  => $item['from_name'],
                    'fromEmail' => $item['from_email'],
                    'toEmail'   => $item['to_email'],
                    'toName'    => $item['to_name'],
                    'body'      => $item['body'],
                );

                $isError = false;
                try {
                    $sender->send($data);

                } catch (Exception $e) {
                    $isError = true;
                    $errors ++;
                    $errCode = $e->getCode();
                    $errMsg = $e->getMessage();
                    echo "\n[ERROR] {$errCode}:  {$errMsg}! While sending to: {$item['to_email']}\n";
                    ob_flush();

                    // Зафиксировать в базе ошибку отправки сообщения
                    if($item['subscriber'] > 0) {
                        $tmpData = array(
                            'last_error' => mb_substr("{$errCode}:  {$errMsg}", 0, 253),
                        );
                        cot::$db->update(subscribe_model_Subscriber::tableName(), $tmpData, "id={$item['subscriber']}");
                    }

                    echo "\n------------\n";
                    ob_flush();

                }

                // Убираем обработанный элемент из очереди
                cot::$db->delete(subscribe_model_Queue::tableName(), "id={$item['id']}");

                if (!$isError) {
                    echo " done\n";
                    $i++;
                }
                unset($item);
                if($i % 100 == 0) gc_collect_cycles();
            }
        }

        $stmtItems->closeCursor();

        // Освободим блокировку
        file_put_contents($pidFile, '0');

        echo "$i letters send\n";

        return '';
    }
}

