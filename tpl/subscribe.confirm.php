<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * User confirm email template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/** @var subscribe_model_Subscriber $subscriber */
$subscriber = $this->subscriber;

?>
<h1><?=htmlspecialchars($this->page_title)?></h1>

<?php
// Error and message handling
$this->displayMessages();
?>

<?php if(!empty($subscriber)) {
    echo sprintf(cot::$L['subscribe_unsubscribe_tip'], $subscriber->unsubscribeUrl());
}
