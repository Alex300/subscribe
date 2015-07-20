<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * Subscribe admin Edit template
 *
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 *
 * @note Поля формы можно выводить и "поштучно". Но мне в цикле оказалось гораздо удобнее
 */

global $Ldt;

/** @var subscribe_model_Subscribe $item */
$item = $this->subscribe;

// Error and message handling
$this->displayMessages();

?>
<div class="panel panel-default">
    <div class="panel-heading"><?=$item->title?></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p>ID: #<?=$item->id?></p>
                <p>
                    <?=cot::$L['Status']?>:
                    <?php if($item->is_running) { ?>
                        <span class="label label-primary"><?=cot::$L['subscribe_running']?></span>
                    <?php }

                    if($item->active) { ?>
                        <span class="label label-success"><?=cot::$L['Enabled']?></span>
                    <?php } else { ?>
                        <span class="label label-default"><?=cot::$L['Disabled']?></span>
                    <?php }

                    if($item->periodical) { ?>
                        <span class="label label-info"><?=cot::$L['subscribe_periodical']?></span>
                    <?php } ?>
                </p>
                <p>
                    <?=cot::$L['subscribe_subscribers']?>:
                    <a href="<?=cot_url('admin', array('m'=>'subscribe', 'n'=>'user', 'f[sid]'=>$item->id))?>">
                        <?=$this->subscribers?>
                    </a><br />

                    <?=cot::$L['subscribe_active_subscribers']?>:
                    <a href="<?=cot_url('admin', array('m'=>'subscribe', 'n'=>'user', 'f[sid]'=>$item->id, 'f[active]'=>1))?>">
                        <?=$this->activeSubscribers?>
                    </a>
                </p>
                <p>
                    <?php
                    $lastEx = '<span class="fa fa-minus"></span>';
                    if(!empty($item->last_executed)) {
                        $tmp = strtotime('1970-01-02 00:01:00');
                        $tmp2 = strtotime($item->last_executed);
                        if ($tmp2 > $tmp) $lastEx = cot_date($Ldt['datetime_full'], $tmp2);
                    }
                    ?>
                    <?=cot::$L['subscribe_last_executed']?>: <?=$lastEx?>
                    <br /><?=cot::$L['subscribe_last_sent']?>: <?=$item->last_sent?>
                </p>
            </div>

            <div class="col-xs-12 col-md-6">
                <?=nl2br($item->admin_note)?>
            </div>
        </div>
        <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'edit', 'id' => $item->id))?>" class="btn btn-default">
            <span class="fa fa-edit"></span> <?=cot::$L['Edit']?> </a>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <table class="">
            <tr>
                <td><?=$item::fieldLabel('from_title')?>: </td>
                <td class="paddingleft10 strong"><?=$this->data['fromName']?></td>
            </tr>
            <tr>
                <td><?=$item::fieldLabel('from_mail')?>: </td>
                <td class="paddingleft10 strong"><?=$this->data['fromEmail']?></td>
            </tr>
            <tr>
                <td><?=$item::fieldLabel('subject')?>: </td>
                <td class="paddingleft10 strong"><?=$this->data['subject']?></td>
            </tr>
        </table>

        <hr />
        <div><?=$this->data['text']?></div>
    </div>
</div>
