<?php
defined('COT_CODE') or die('Wrong URL.');

if(empty($GLOBALS['db_subscribe'])) {
    cot::$db->registerTable('subscribe');
    cot_extrafields_register_table('subscribe');
}

/**
 * Модель subscribe_model_Subscribe
 *
 * Модель Рассылки
 *
 * @method static subscribe_model_Subscribe getById($pk);
 * @method static subscribe_model_Subscribe fetchOne($conditions = array(), $order = '')
 * @method static subscribe_model_Subscribe[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int    $id             id
 * @property string $title          Заголовок
 * @property string $alias          Алияс
 * @property string $from_mail      От кого e-mail
 * @property string $from_title     От кого - заголовок
 * @property string $subject        Тема письма
 * @property string $description    Описание рассылки
 * @property string $admin_note
 * @property string $content_url    Урл, где брать контент
 * @property string $text           Текст. Используется, если не установлен content_url
 * @property string $last_executed  Время последнего запуска
 * @property string $last_sent      Количество писем, разосланных в последний раз
 * @property string $next_run       Время следующего запуска. Устанавливается вручную или расчитывается на основе расписания.
 * @property string $sched_mday     Расписние. Дни месяца в формате 1,8,10, 19-25, 27-30
 * @property string $sched_wday     Расписание. Дни недели. Номера через запятую.
 * @property string $sched_time     Расписание. Время в формате 10, 16:45
 * @property bool   $active         Рассылка включена?
 * @property bool   $periodical     Периодическая?
 * @property bool   $running        Запущено ли в данный момент?
 * @property int    $sort           Порядок сортировки
 * @property int    $ping
 * @property int    $created        Дата создания
 * @property int    $created_by     Кем создано
 * @property int    $updated        Дата изменения
 * @property int    $updated_by     Кем изменено
 *
 */
class subscribe_model_Subscribe extends Som_Model_Abstract
{
    /**
     * @var Som_Model_Mapper_Abstract
     */
    protected  static $_db = null;
    protected  static $_tbname = '';
    protected  static $_primary_key = 'id';

    public static $fetchColumns = array();
    public static $fetchJoins = array();

    /**
     * Static constructor
     */
    public static function __init($db = 'db'){
        static::$_tbname = cot::$db->subscribe;
        parent::__init($db);
    }

    public function __clone(){
        $this->_oldData = array();

        $this->_data['id'] = null;
        $this->_data['alias'] = '';
        $this->_data['active'] = 0;
        $this->_data['last_executed'] = '1970-01-01 00:00:00';
        $this->_data['last_sent'] = 0;
        $this->_data['next_run'] = '';
        $this->_data['is_running'] = 0;
        $this->_data['created'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['created_by'] = cot::$usr['id'];
        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];
    }

    /**
     * Дата следующего запуска
     * @return bool|string
     */
    public function getNextRunDate() {
        return static::nextRunDate($this->_data['sched_mday'], $this->_data['sched_wday'], $this->_data['sched_time']);
    }

    /**
     * Получить дату следующего запуска рассылки
     * @param string $monthDays
     * @param string $weekDays
     * @param string $time
     * @return bool|string
     */
    public static function nextRunDate($monthDays, $weekDays, $time) {

        $nowDate = getdate(cot::$sys['now']);
        $timeZone = cot::$usr['timezone'];

        // Парсим дни месяца:
        $mdays = array();
        $wdays = array();
        $times = array();

        $monthDays = str_replace(' ', '', $monthDays);
        $weekDays = str_replace(' ', '', $weekDays);
        $time = str_replace(' ', '', $time);

        $tmp = explode(',', $monthDays);
        foreach ($tmp as $day) {
            $day = trim($day);
            if (!preg_match("/[\d-]+/", $day))
                continue;
            if (strcmp((int) $day, $day) === 0 && mb_stripos($day, '-') === false) {
                $mdays[] = (int) $day;
            } else {
                $tmp2 = explode('-', $day);
                if (is_array($tmp2) && isset($tmp2[1])) {
                    if ($tmp2[0] == '')
                        $tmp2[0] = 1;
                    if ($tmp2[1] == '')
                        $tmp2[1] = 31;
                    for ($i = $tmp2[0]; $i <= $tmp2[1]; $i++) {
                        $mdays[] = $i;
                    }
                }
            }
        }
        $mdays = array_unique($mdays);

        $tmp = explode(',', $weekDays);
        foreach ($tmp as $day) {
            $day = trim($day);
            if (!preg_match("/[\d-]+/", $day))
                continue;
            $wdays[] = (int) $day;
        }

        $tmp = explode(',', $time);
        foreach ($tmp as $t) {
            $t = trim($t);
            if (!preg_match("/[\d:]+/", $t))
                continue;
            $times[] = $t;
        }
        // Если дни месяца не указаны
        if (count($mdays) == 0) {
            for ($i = 1; $i <= 31; $i++) {
                $mdays[] = $i;
            }
        }

        // Если дни недели не указаны
        // ISO-8601 numeric representation of the day of the week
        if (count($wdays) == 0) {
            for ($i = 1; $i <= 7; $i++) {
                $wdays[] = $i;
            }
        }

        // Если время не указано
        if (count($times) == 0) {
            $times[] = '00:01';
        }
        //var_dump($mdays);
        // Ищем ближайшую дату в течение ближайших 100 лет
        // TODO оптимизировать
        for ($i = 0; $i <= 36500; $i++) {
            $fst = explode(':', $times[0]);
            $tmpT = mktime($fst[0] - $timeZone, $fst[1], 0, $nowDate['mon'], $nowDate['mday'] + $i, $nowDate['year']);

            if (!in_array(cot_date('d', $tmpT), $mdays)) continue;
            if (!in_array(cot_date('N', $tmpT), $wdays)) continue;

            // Найдена следующая дата
            if ($tmpT > cot::$sys['now']) {
                return date('Y-m-d H:i', $tmpT);
            }
            // Если в расписании сегодня:
            if (count($times) <= 1) continue;

            // Перебираем расписание по времени дня
            foreach ($times as $tim) {
                $tim = explode(':', $tim);
                $tmpT = mktime($tim[0] - $timeZone, $tim[1], 0, $nowDate['mon'], $nowDate['mday'] + $i, $nowDate['year']);
                if ($tmpT > cot::$sys['now']) {
                    return date('Y-m-d H:i', $tmpT);
                }
            }
        }

        // Если дата не найдена, то через 100 лет ))
        return date('Y-m-d H:i', mktime($fst[0], $fst[1], 0, $nowDate['mon'], $nowDate['mday'] + $i, $nowDate['year']));
    }

    protected function beforeUpdate(){
        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];

        return parent::beforeUpdate();
    }

    protected function beforeDelete(){

        // Удалить всех подписчиков
        $items = subscribe_model_Subscriber::find(array(
            array('subscribe', $this->_data['id'])
        ));
        if(!empty($items)){
            foreach($items as $itemRow){
                $itemRow->delete();
            }
        }

        return parent::beforeDelete();
    }


//    public function beforeSave(&$data = null) {
//
//        return parent::beforeSave($data);
//    }

    public static function fieldList(){
        $fields = array (
            'id' =>
                array (
                    'type' => 'int',
                    'description' => 'id',
                    'primary' => true,
                ),
            'title' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Title'],
                ),
            'alias' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['Alias'],
                ),
            'from_mail' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_from_mail'],
                ),
            'from_title' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_from_title'],
                ),
            'subject' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_subject'],
                ),
            'description' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['subscribe_description'],
                ),
            'admin_note' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['subscribe_admin_note']
                ),
            'content_url' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_content_url'],
                ),
            'text' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['Text'],
                ),
            'last_executed' =>
                array (
                    'type' => 'datetime',
                    'default' => null,
                    'description' => cot::$L['subscribe_last_executed'],
                ),
            'last_sent' =>
                array (
                    'type' => 'int',
                    'default' => 0,
                    'description' => cot::$L['subscribe_last_sent'],
                ),
            'next_run' =>
                array (
                    'type' => 'datetime',
                    'default' => null,
                    'description' => cot::$L['subscribe_next_run'],
                ),
            'sched_mday' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_sched_mday'],
                ),
            'sched_wday' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_sched_wday'],
                ),
            'sched_time' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_sched_time'],
                ),
            'active' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['subscribe_active'],
                ),
            'periodical' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['subscribe_periodical'],
                ),
            'running' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['subscribe_running'],
                ),
            'sort' =>
                array (
                    'type' => 'int',
                    'default' => 10,
                    'description' => cot::$L['subscribe_sort'],
                ),
            'ping' =>
                array (
                    'type' => 'datetime',
                    'default' => null,
                ),
            'created' =>
                array (
                    'type' => 'datetime',
                    'default' => date('Y-m-d H:i:s', cot::$sys['now']),
                    'description' => cot::$L['subscribe_created'],
                ),
            'created_by' =>
                array (
                    'type' => 'int',
                    'default' => cot::$usr['id'],
                    'description' => cot::$L['subscribe_created_by'],
                ),
            'updated' =>
                array (
                    'type' => 'datetime',
                    'default' => date('Y-m-d H:i:s', cot::$sys['now']),
                    'description' => cot::$L['subscribe_updated'],
                ),
            'updated_by' =>
                array (
                    'type' => 'int',
                    'default' => cot::$usr['id'],
                    'description' => cot::$L['subscribe_updated_by'],
                ),
        );

        return $fields;
    }
}

subscribe_model_Subscribe::__init();