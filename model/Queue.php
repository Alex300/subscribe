<?php
defined('COT_CODE') or die('Wrong URL.');

if(empty($GLOBALS['db_subscribe_queue'])) {
    cot::$db->registerTable('subscribe_queue');
}

/**
 * Модель subscribe_model_Queue
 *
 * Модель очереди отправлений
 *
 * @method static subscribe_model_Queue getById($pk);
 * @method static subscribe_model_Queue fetchOne($conditions = array(), $order = '')
 * @method static subscribe_model_Queue[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int     $id             id
 * @property subscribe_model_Subscribe   $subscribe  Рассылка
 * @property subscribe_model_Subscriber  $subscriber Подписчик
 * @property string  $to_email
 * @property string  $to_name       Имя получателя
 * @property string  $from_email
 * @property string  $from_name     Имя отправителя
 * @property string  $subject
 * @property string  $body
 * @property string  $created       Дата создания
 */
class subscribe_model_Queue extends Som_Model_Abstract
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
        static::$_tbname = cot::$db->subscribe_queue;
        parent::__init($db);
    }

    protected function beforeInsert() {
        if(empty($this->_data['created']))      $this->_data['created'] = date('Y-m-d H:i:s', cot::$sys['now']);

        return parent::beforeInsert();
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
                    'default'     => 0,
                    'description' => cot::$L['subscribe_subscribe'],
                    'link'        =>
                        array(
                            'model'    => 'subscribe_model_Subscribe',
                            'relation' => SOM::TO_ONE,
                            'label'    => 'title',
                        ),
                ),
            'subscriber' =>
                array(
                    'type'        => 'link',
                    'default'     => 0,
                    'description' => cot::$L['subscribe_subscribe'],
                    'link'        =>
                        array(
                            'model'    => 'subscribe_model_Subscriber',
                            'relation' => SOM::TO_ONE,
                            'label'    => 'email',
                        ),
                ),
            'to_email' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['Email'],
                ),
            'to_name' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'description' => cot::$L['Name'],
                ),
            'from_email' =>
                array (
                    'type' => 'varchar',
                    'length' => '255',
                    'default' => '',
                    'nullable' => false,
                    'description' => cot::$L['subscribe_from_mail'],
                ),
            'from_name' =>
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
            'body' =>
                array (
                    'type' => 'text',
                    'default' => '',
                    'description' => cot::$L['Text'],
                ),
            'created' =>
                array (
                    'type' => 'datetime',
                    'default' => date('Y-m-d H:i:s', cot::$sys['now']),
                    'description' => cot::$L['subscribe_created'],
                ),
        );

        return $fields;
    }
}

subscribe_model_Queue::__init();