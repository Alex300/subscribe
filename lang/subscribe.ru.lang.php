<?php
/**
 * Subscribe module for Cotonti Siena
 *     Russian Lang file
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');

/**
 * Module Title & Subtitle
 */
$L['info_name'] = 'Рассылки';
$L['info_desc'] = 'Модуль рассылок';

/**
 * Module Body
 */
$L['subscribe_confirm'] = 'Подтверждение подписки на рассылку';
$L['subscribe_confirmed'] = '<p>Ваш e-mail <strong>%1$s</strong> подтвержден.</p><p>Подписка на рассылку <strong>«%2$s»</strong> активирована.</p>';
$L['subscribe_my'] = 'Мои подписки';
$L['subscribe_my_page'] = 'Моя страница';
$L['subscribe_subscribe_title'] = 'Рассылки';
$L['subscribe_subscribe'] = 'Рассылка';
$L['subscribe_subscribes'] = 'Рассылки';
$L['subscribe_subscribers'] = 'Подписчики';
$L['subscribe_to_subscribe'] = 'Подписаться';
$L['subscribe_unsubscribe'] = 'Отписаться от рассылки';
$L['subscribe_unsubscribe_tip'] = 'Чтобы отписать от рассылки пройдите по этой адресу: <a href="<%1$s>">%1$s</a>';
$L['subscribe_user_subscribes'] = 'Подписки пользователя';
$L['subscribe_you_subscribed'] = 'Вы подписаны';
$L['subscribe_you_not_subscribed'] = 'Вы не подписаны';

//$Ls['advert_advertisement'] = "объявление,объявления,объявлений";


/**
 * Errors and messages
 */
$L['subscribe_delete_confirm'] = 'Удалить рассылку и всех ее подписчиков? Данное действие нельзя отменить!';
$L['subscribe_deleted'] = 'Рассылка «%1$s» удалена';
$L['subscribe_wait_confirm'] = 'На указанный email отправлено письмо и инструкцией по активации подписки';
$L['subscribe_subscriber_deleted'] = 'Подписчик с email «%1$s» удален';
$L['subscribe_msg_you_subscribed'] = 'Вы подписаны на рассылку «%1$s».';
$L['subscribe_msg_you_unsubscribed'] = 'Вы отписаны от рассылки «%1$s».';

$L['subscribe_err_not_found'] = 'Рассылка не найдена';
$L['subscribe_err_disabled'] = 'Рассылка отключена';
$L['subscribe_err_subscriber_not_found'] = 'Подписчик не найден';
$L['subscribe_err_user_not_found'] = 'Пользователь не найден';
$L['subscribe_err_user_subscribed'] = 'Пользователь с email «%1$s» уже подписан на «%2$s»';
$L['subscribe_err_wrong_confirm_code'] = "Не верный код подтверждения";
$L['subscribe_err_wrong_unsubscribe_code'] = "Не верный код отписки";
$L['subscribe_err_wrongmail'] = "Ошибочный e-mail";


/**
 * Admin Part
 */
$L['subscribe_admin_note'] = 'Примечание администратора';
$L['subscribe_active'] = 'Включена';
$L['subscribe_active_subscribers'] = 'Активные подписчики';
$L['subscribe_add_new'] = 'Создать рассылку';
$L['subscribe_add_new_subscriber'] = 'Добавить подписчика';
$L['subscribe_content_url'] = 'Урл адрес контента';
$L['subscribe_content_url_hint'] = 'Текст рассылки берется по этому адресу или из поля «Текст»';
$L['subscribe_created'] = 'Создно';
$L['subscribe_created_by'] = 'Кем создано';
$L['subscribe_description'] = 'Описание рассылки';
$L['subscribe_email_not_validated'] = 'Email не подтвержден';
$L['subscribe_email_validated'] = 'Email подтвержден';
$L['subscribe_mails'] = 'Писем';
$L['subscribe_extrafields'] = 'Экстраполя для рассылок';
$L['subscribe_extrafields_subscriber'] = 'Экстраполя для подписчиков';
$L['subscribe_from_mail'] = 'От кого e-mail';
$L['subscribe_from_mail_hint'] = 'Значение поля «Отправитель» в письмах рассылки';
$L['subscribe_from_title'] = 'От кого - заголовок';
$L['subscribe_last_error'] = 'Последняя ошибка';
$L['subscribe_last_executed'] = 'Время последнего запуска';
$L['subscribe_last_sent'] = 'Последний раз разослано писем';
$L['subscribe_last_sent_time'] = 'Время последней отправки';
$L['subscribe_mday'] = 'Дни месяца';
$L['subscribe_next_run'] = 'Время следующего запуска';
$L['subscribe_next_run_hint'] = 'Устанавливается вручную или расчитывается на основе расписания (например для периодических рассылок). Очистите это поле, если Вы меняете расписание.';
$L['subscribe_periodical'] = 'Периодическая';
$L['subscribe_queue'] = 'Очередь';
$L['subscribe_running'] = 'Выполняется';
$L['subscribe_schedule'] = 'Расписание';
$L['subscribe_sched_mday'] = 'Расписние. Дни месяца';
$L['subscribe_sched_mday_hint'] = 'Дни месяца в формате 1,8,10, 19-25, 27-30';
$L['subscribe_sched_time'] = 'Расписание. Время';
$L['subscribe_sched_time_hint'] = 'Время в формате 10, 16:45.';
$L['subscribe_sched_wday'] = 'Расписание. Дни недели';
$L['subscribe_sched_wday_hint'] = 'Дни недели. Номера через запятую.';
$L['subscribe_sort'] = 'Порядок сортировки';
$L['subscribe_subject'] = 'Тема письма';
$L['subscribe_subscribed'] = 'Подписан';
$L['subscribe_text_hint'] = 'теги:<br />
<b>[URL#http://адрес/контента#]</b> - будет заменено на контент, полученный по этому адресу<br />
<b>[UNSUBSCRIBE_URL]</b> - будет заменено на ссылку «отписаться» для данного подписчика<br />
<b>[YEAR]</b> - будет заменено на текущий год<br />';
$L['subscribe_unsubscr_code'] = 'Код для отписки';
$L['subscribe_updated'] = 'Изменено';
$L['subscribe_updated_by'] = 'Кем изменено';
$L['subscribe_wday'] = 'Дни недели';

/**
 * Module Config
 */
$L['cfg_guestConfirmMail'] = 'Гости должны подтвердить email';
$L['cfg_guestConfirmMail_hint'] = 'Незарегистрированному пользователю будет отправлен email для подтверждения адреса';
$L['cfg_useQueue'] = 'Использовать очередь отправки';
$L['cfg_useQueue_hint'] = 'Письма отсылаются равномерно в порядке очереди.';
$L['cfg_queueCount'] = 'За один раз отправлять';
$L['cfg_queueCount_hint'] = 'За один раз отправляется указанное число писем из очереди. Если очередь отключена, то письма
рассылаются всем подписчикам сразу';