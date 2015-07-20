--
-- Структура таблицы `cot_subscribe`
--

CREATE TABLE IF NOT EXISTS `cot_subscribe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT '',
  `from_mail` varchar(255) DEFAULT '' COMMENT 'От кого e-mail',
  `from_title` varchar(255) DEFAULT '' COMMENT 'От кого - заголовок',
  `subject` varchar(255)  DEFAULT '',
  `description` text  DEFAULT '' COMMENT 'Описание рассылки',
  `admin_note` TEXT NULL DEFAULT '',
  `content_url` varchar(255) DEFAULT '',
  `text` text DEFAULT '' COMMENT 'Текст. Используется, если не установлен content_url',
  `last_executed` datetime DEFAULT '1970-01-01 00:00:00',
  `last_sent` int DEFAULT 0 COMMENT 'Количество писем, разосланных в последний раз',
  `next_run` datetime DEFAULT '1970-01-01 00:00:00' COMMENT 'Время следующего запуска. Устанавливается вручную или расчитывается на основе расписания.',
  `sched_mday` varchar(255) DEFAULT '' COMMENT 'Расписние. Дни месяца в формате 1,8,10, 19-25, 27-30',
  `sched_wday` varchar(255) DEFAULT '' COMMENT 'Расписание. Дни недели. Номера через запятую.',
  `sched_time` varchar(255) DEFAULT '' COMMENT 'Расписание. Время в формате 10, 16:45',
  `active` tinyint(1) DEFAULT 0 COMMENT 'Рассылка включена?',
  `periodical` tinyint(1) DEFAULT 0,
  `running` tinyint(1) DEFAULT 0 COMMENT 'Запущено ли в данный момент?',
  `sort` int(11) DEFAULT 10,
  `ping` datetime DEFAULT '1970-01-01 00:00:00',
  `created` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT '0',
  `updated` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active_idx` (`active`),
  KEY `periodical_idx` (`periodical`),
  KEY `running_idx` (`running`)
) ENGINE=InnoDB ;


--
-- Структура таблицы `cot_subscriber`
--

CREATE TABLE IF NOT EXISTS `cot_subscriber` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribe` int(10) unsigned NOT NULL,
  `user` bigint(20) DEFAULT '0',
  `user_group` int(11) DEFAULT '0' COMMENT 'Группа пользователей (для рассылки всей группе)',
  `email` varchar(255)  NOT NULL,
  `name` varchar(255) DEFAULT '' COMMENT 'Имя получателя',
  `last_executed` datetime DEFAULT '1970-01-01 00:00:00' COMMENT 'Время последней отправки письма по данной подписке.',
  `last_error` varchar(255) DEFAULT '' COMMENT 'Последняя ошибка при выполнении рассылки',
  `active` tinyint(1) DEFAULT '0' COMMENT 'Включена ли данная подписка',
  `params` text DEFAULT '' COMMENT 'Разное служебное инфо',
  `ip` varchar(100)  DEFAULT '' COMMENT 'ip адрес на момент подписки',
  `unsubscr_code` varchar(255) DEFAULT '' COMMENT 'Код для отписки от рассылки, если подписчик проходит по ссылке "отписаться"',
  `email_valid` tinyint(1) DEFAULT '0' COMMENT 'Email подтвержден?',
  `email_valid_date` datetime DEFAULT NULL COMMENT 'Дата подтверждения emaila',
  `created` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT 0,
  `updated` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `active_idx` (`active`),
  FOREIGN KEY (`subscribe`) REFERENCES cot_subscribe(`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB ;

--
-- Структура таблицы `cot_subscribe_queue`
--

CREATE TABLE IF NOT EXISTS `cot_subscribe_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribe` int unsigned NOT NULL,
  `subscriber` bigint unsigned NOT NULL,
  `to_email` varchar(255) DEFAULT '' COMMENT 'Кому e-mail',
  `to_name` varchar(255) DEFAULT '' COMMENT 'Кому - имя',
  `from_email` varchar(255) DEFAULT '' COMMENT 'От кого e-mail',
  `from_name` varchar(255) DEFAULT '' COMMENT 'От кого - имя',
  `subject` varchar(255)  DEFAULT '',
  `body` text DEFAULT '' COMMENT 'Тело письма',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;