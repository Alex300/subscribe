<?php
/**
 * Subscribe module for Cotonti Siena
 * Queue sender
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class subscribe_sender_Queue extends subscribe_sender_Abstract {

    public function send($data) {
        global $cot_import_filters;

        if(empty($data['fromName']))  $data['fromName'] = cot::$cfg['maintitle'];
        if(empty($data['fromEmail']))  $data['fromEmail'] = cot::$cfg['adminemail'];

        $queue = new subscribe_model_Queue();

        // Отключим html-фильтры
        $tmp = $cot_import_filters['HTM'] = array();

        $queue->from_email  = $data['fromEmail'];
        $queue->from_name   = $data['fromName'];
        $queue->to_email    = $data['toEmail'];
        $queue->to_name     = $data['toName'];
        $queue->subject     = $data['subject'];
        $queue->body        = $data['body'];
        $queue->subscribe   = intval($data['subscribe']);
        $queue->subscriber  = intval($data['subscriber']);

        $queue->save();

        unset($queue);

        $cot_import_filters['HTM'] = $tmp;

        return true;
    }
}