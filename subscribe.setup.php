<?php
/* ====================
[BEGIN_COT_EXT]
Code=subscribe
Name=Subscribe
Description=Subscribe module for Cotonti Siena
Version=1.0.0
Date=2015-06-25
Author=Kalnov Alexey    (kalnovalexey@yandex.ru)
Copyright=(с) 2015 Portal30 Studio http://portal30.ru
Auth_guests=R
Lock_guests=12345A
Auth_members=RW
Requires_plugins=cotonti-lib
Recommends_plugins=pagelist
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
guestConfirmMail=02:radio::1:Guest must confirm email
useQueue=04:radio::1:Use mailing queue
queueCount=06:string::100:
[END_COT_EXT_CONFIG]
==================== */

/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 * Поставить права на запись на файл modules/subscribe/inc/queue.txt
 *
 * cot_url('subscribe') - активные периодические рассылки. Авторизованные пользователи могут подписаться
 * cot_url('subscribe', array('m'=>'user')) - мои подписки пользователя
 * cot_url('subscribe', array('m'=>'user', 'uid'=1)) - подписки пользователя с id=1
 *
 * Запуск текущих рассылок
 * php cli.php --a subscribe.main.run > subscribe.log
 *
 * Запуск обработки очереди
 * php cli.php --a subscribe.main.runQueue > subscribeQueue.log
 *
 * @todo Возможность выбора пользователя
 * @todo Возможность выбора группы пользователей для рассылки
 * @todo Поддержка экстраполей в дефолных шаблонах
 * @todo Клонирование рассылки должно копировать всех подписчиков
 */
defined('COT_CODE') or die('Wrong URL');

