<?php
/**
 * Subscribe main admin template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */
?>
<div class="button-toolbar">
    <a title="<?=cot::$L['Configuration']?>" href="<?=cot_url('admin', 'm=config&n=edit&o=module&p=subscribe')?>"
       class="btn btn-default marginbottom10"><span class="fa fa-wrench"></span> <?=cot::$L['Configuration']?></a>

    <a href="<?=cot_url('admin', array('m'=>'extrafields', 'n'=> cot::$db->subscribe))?>" class="btn btn-default marginbottom10"><span
            class="fa fa-check-square-o"></span> <?=cot::$L['subscribe_extrafields']?></a>

    <a href="<?=cot_url('admin', array('m'=>'extrafields', 'n'=> cot::$db->subscriber))?>" class="btn btn-default marginbottom10"><span
            class="fa fa-check-square-o"></span> <?=cot::$L['subscribe_extrafields_subscriber']?></a>

    <a href="<?=cot_url('admin', array('m' => 'subscribe'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-envelope-o"></span> <?=cot::$L['subscribe_subscribes']?></a>

    <a href="<?=cot_url('admin', array('m' => 'subscribe', 'n' => 'user'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-users"></span> <?=cot::$L['subscribe_subscribers']?></a>

    <a href="<?=cot_url('admin', array('m' => 'subscribe', 'n' => 'queue'))?>" class="btn btn-default marginbottom10">
        <span class="fa fa-tasks"></span> <?=cot:: $L['subscribe_queue']?></a>

</div>

<?php
// Error and message handling
echo $this->displayMessages();

echo $this->content;