<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
abstract class subscribe_sender_Abstract {

//    public $subscribe = 0;
//
//    public $subscriber = 0;
//
//    public $subject = '';
//
//    public $email = '';
//
//    public $name = '';
//
//    public $fromTitle = '';
//
//    public $fromMail = '';
//
//    public $text = '';

    /**
     * Отправить письмо
     * @param array $data(
     *  'subscribe' => 0,
     *  'subscriber' => 0,
     *  'subject' => '',
     *  'toEmail' => '',
     *  'toName' => '',
     *  'fromName' => '',
     *  'fromEmail' => '',
     *  'body' => ''
     * );
     *
     * @return mixed
     */
    public abstract function send($data);
}