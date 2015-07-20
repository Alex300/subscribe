<?php
defined('COT_CODE') or die('Wrong URL.');

if(empty($GLOBALS['db_subscriber'])) {
    cot::$db->registerTable('subscriber');
    cot_extrafields_register_table('subscriber');
}

/**
 * Модель subscribe_model_Subscriber
 *
 * Модель подписчика
 *
 * @method static subscribe_model_Subscriber getById($pk);
 * @method static subscribe_model_Subscriber fetchOne($conditions = array(), $order = '')
 * @method static subscribe_model_Subscriber[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int     $id             id
 * @property subscribe_model_Subscribe   $subscribe Рассылка
 * @property int     $user          id пользователя
 * @property int     $user_group    Группа пользователей (для рассылки всей группе)
 * @property string  $email
 * @property string  $name          Имя получателя
 * @property string  $last_executed Время последней отправки письма по данной подписке.
 * @property string  $last_error    Последняя ошибка при выполнении рассылки
 * @property bool    $active        Включено?
 * @property string  $params        Разное служебное инфо
 * @property string  $ip            ip адрес на момент подписки
 * @property string  $unsubscr_code Код для отписки от рассылки, если подписчик проходит по ссылке "отписаться"
 * @property bool    $email_valid   Email подтвержден?
 * @property string  $email_valid_date Дата подтверждения email'а
 * @property string  $created       Дата создания
 * @property int     $created_by    Кем создано
 * @property string  $updated       Дата изменения
 * @property int     $updated_by    Кем изменено
 *
 */
class subscribe_model_Subscriber extends Som_Model_Abstract
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
        static::$_tbname = cot::$db->subscriber;
        parent::__init($db);
    }

    public function __clone(){
        $this->_oldData = array();

        $this->_data['id'] = null;
        $this->_data['active'] = 0;
        $this->_data['last_executed'] = '1970-01-01 00:00:00';
        $this->_data['last_error'] = 0;
        $this->_data['ip'] = cot::$usr['ip'];
        $this->_data['created'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['created_by'] = cot::$usr['id'];
        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];
    }

    /**
     * Урл для отписки
     * @return string
     */
    public function unsubscribeUrl() {
        $ret = cot_url('subscribe', array('m'=>'user', 'a'=>'unsubscribe', 'code'=>$this->_data['unsubscr_code']));

        if(!cot_url_check($ret)) $ret = cot::$cfg['mainurl'].'/'.$ret;

        return $ret;
    }

    protected function beforeInsert() {

        // Удаление старых неподтвержденных подписчиков
        // Update пока не трогаем
        $this->garbageCollect();

        if(empty($this->_data['ip']))           $this->_data['ip'] = cot::$usr['ip'];
        if(empty($this->_data['created']))      $this->_data['created'] = date('Y-m-d H:i:s', cot::$sys['now']);
        if(empty($this->_data['created_by']))   $this->_data['created_by'] = cot::$usr['id'];
        if(empty($this->_data['updated']))      $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        if(empty($this->_data['updated_by']))   $this->_data['updated_by'] = cot::$usr['id'];

        // Код для отписки
        if(empty($this->_data['unsubscr_code'])) $this->_data['unsubscr_code'] = static::generateUnsubscribeCode();

        return parent::beforeInsert();
    }

    protected function beforeUpdate(){
        $this->_data['updated'] = date('Y-m-d H:i:s', cot::$sys['now']);
        $this->_data['updated_by'] = cot::$usr['id'];

        return parent::beforeUpdate();
    }

//    protected function beforeSave(&$data = null) {
//
//
//        return parent::beforeSave($data);
//    }

    /**
     * Сгенерировать код для отписки от рассылки
     */
    public static function generateUnsubscribeCode() {
        for($i = 0; $i<=1000; $i++) {
            $code = md5(microtime() + $i);
            $cnt = subscribe_model_Subscriber::count(array(array('unsubscr_code', $code)));
            if($cnt == 0) return $code;
        }

        return '';
    }

    /**
     * Сборщик мусора
     */
    protected function garbageCollect() {

        // Удаление старых неподтвержденных подписчиков
        $delDate  = new DateTime();
        // 1 неделя
        $delDate->sub(new DateInterval('P1W'));

        $items = subscribe_model_Subscriber::find(array(
            array('email_valid', 0),
            array('created', $delDate->format('Y-m-d H:i:s'), '<=')
        ));
        if(!empty($items)) {
            foreach($items as $itemRow) {
                $itemRow->delete();
            }
        }
    }

    public static function fieldList(){
        $fields = array (
            'id' =>
                array (
                    'type' => 'int',
                    'description' => 'id',
                    'primary' => true,
                ),
            'subscribe' =>
                array(
                    'type'        => 'link',
                    'nullable'    => false,
                    'default'     => 0,
                    'description' => cot::$L['subscribe_subscribe'],
                    'link'        =>
                        array(
                            'model'    => 'subscribe_model_Subscribe',
                            'relation' => SOM::TO_ONE,
                            'label'    => 'title',
                        ),
                ),
            'user' =>
                array (
                    'type' => 'int',
                    'description' => cot::$L['User'],
                    'default' => 0,
                ),
            'user_group' =>
                array (
                    'type' => 'int',
                    'description' => cot::$L['Group'],
                    'default' => 0,
                ),
            'email' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Email'],
                ),
            'name' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['Name'],
                ),
            'last_executed' =>
                array (
                    'type' => 'datetime',
                    'default' => null,
                    'description' => cot::$L['subscribe_last_executed'],
                ),
            'last_error' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['subscribe_last_error'],
                ),
            'active' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    'description' => cot::$L['Enabled'],
                ),
            'params' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => 'Служебная информация',
                ),
            'ip' =>
                array (
                    'type' => 'varchar',
                    'length' => '100',
                    'default' => cot::$usr['ip'],
                    'description' => cot::$L['Ip'],
                ),
            'unsubscr_code' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['subscribe_unsubscr_code'],
                ),
            'email_valid' =>
                array (
                    'type' => 'tinyint',
                    'length' => '1',
                    'default' => 0,
                    //'description' => '',
                ),
            'email_valid_date' =>
                array (
                    'type' => 'datetime',
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

subscribe_model_Subscriber::__init();