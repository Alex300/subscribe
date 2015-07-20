<?php
/**
 * Subscribe module for Cotonti Siena
 * Письмо подтверждение email'а подписчика
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

/** @var subscribe_model_Subscribe $subscribe */
$subscribe = $this->subscribe;

/** @var subscribe_model_Subscriber $subscriber */
$subscriber = $this->subscriber;

$name = '';
if(!empty($subscriber->name)) $name = ', '.htmlspecialchars($subscriber->name);
?>
<p>Добрый день<?=$name?>!</p>
<p>На сайте <a href="<?=cot::$cfg['mainurl']?>"><strong><?=cot::$cfg['maintitle']?></strong></a> Вы подписались на рассылку:
    <strong><?=htmlspecialchars($subscribe->title)?></strong></p>
<p>Для активации расслки Вам необходимо подтвердить Ваш e-mail. Для этого перейдите по этой ссылке:</p>
<p><a href="<?=$this->confirmUrl?>"><?=$this->confirmUrl?></a></p>
<p>---</p>
<p><i>Пожалуйста не отвечайте на это письмо</i></p>
<p><i>P.S. Если Вы не оформляли подписку на рассылку на сайте <strong><?=cot::$cfg['maintitle']?></strong> или Вы передумали ее включать,
    просто проигнорируйте это письмо и Ваша подписка будет анулирована</i></p>
