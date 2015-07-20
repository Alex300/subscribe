<?php
/**
 * Subscribe module for Cotonti Siena
 * cot_mail() sender
 *
 * @see cot_mail()
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class subscribe_sender_Cotmail extends subscribe_sender_Abstract {

    public function send($data) {
        if(empty($data['fromName']))   $data['fromName'] = cot::$cfg['maintitle'];
        if(empty($data['fromEmail']))  $data['fromEmail'] = cot::$cfg['adminemail'];

        $fromName = mb_encode_mimeheader($data['fromName'], 'UTF-8', 'B', "\n");

        $headers = "From: \"" . $fromName . "\" <" . $data['fromEmail'] . ">\n" . "Reply-To: <" . cot::$cfg['adminemail'] . ">\n";

        $ret = cot_mail($data['toEmail'], $data['subject'], $data['body'], $headers, false, null, true);

        return $ret;
    }
}