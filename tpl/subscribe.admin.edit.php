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
$item = $this->item;

$labelClass = 'col-xs-12 col-md-3';
$elementClass = 'col-xs-12 col-md-9';

$formElements = $this->formElements;
unset($this->formElements);
?>
<div class="panel panel-default">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <div class="row">
            <div class="<?=$labelClass?> hidden-xs"></div>

            <div class="<?=$elementClass?>">
                <?php if($item->id > 0) { ?>
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

                    <div class="text-right">
                        <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'view', 'id' => $item->id))?>" class="btn btn-default">
                            <span class="fa fa-eye"></span> <?=cot::$L['Preview']?> </a>
                    </div>
                <?php
                }

                ?>
            </div>
        </div>

        <div class="row margintop20">
            <div class="col-xs-12">
                <form action="<?=$this->formAction?>" enctype="multipart/form-data" method="post" name="catalog-form"
                      class="form-horizontal" role="form">
                    <?php
                    echo $formElements['hidden']['element'];
                    foreach($formElements as $fldName => $element) {
                        if($fldName == 'hidden') continue;

                        $elClass = $elementClass;
                        if(empty($element['label'])) $elClass .= ' col-md-offset-3';

                        ?>
                        <div class="form-group <?=cot_formGroupClass($fldName)?>">
                            <?php if(!empty($element['label'])) { ?>
                                <label class="<?=$labelClass?> control-label">
                                    <?=$element['label']?>
                                    <?php if(!empty($element['required'])) echo ' *';?>
                                    :
                                </label>
                            <?php }

                            ?>
                            <div class="<?=$elClass?>">
                                <?php
                                echo $element['element'];
                                if(isset($element['hint']) && $element['hint'] != '') { ?>
                                    <span class="help-block"><?=$element['hint']?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary"><span class="fa fa-floppy-o"></span>
                                <?=cot::$L['Save']?></button>

                            <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'view', 'id' => $item->id))?>" class="btn btn-default">
                                <span class="fa fa-eye"></span> <?=cot::$L['Preview']?> </a>

                            <?php if($item->id > 0) {
                            $delUrlParams = array('m'=>'subscribe', 'a'=>'delete', 'id'=>$item->id);
                            ?>
                            <a href="<?=cot_confirm_url(cot_url('admin', $delUrlParams, '', true))?>" class="btn btn-danger confirmLink">
                                <span class="fa fa-trash-o"></span> <?=cot::$L['Delete']?></a>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
