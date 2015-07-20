<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * Subscribe list admin template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

global $Ldt;

/** @var subscribe_model_Subscribe[] $items */
$items = $this->items;

// Add new adv and edit category buttons
?>
<div class="text-right">
    <a href="<?=$this->addNewUrl?>" class="btn btn-primary btn-sm"><span class="fa fa-plus"></span> <?=cot::$L['subscribe_add_new']?></a>
</div>
<?php

// Фильтры для модератора ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=cot::$L['Filters']?></div>
    <div class="panel-body">
        <form method="get" action="<?=(cot_url('admin', array('m'=>'subscribe')))?>" class="form-inline">
            <?=$this->filterForm['hidden']?>

            <div class="form-group">
                <label>ID</label>
                <?=cot_inputbox('text', 'f[id]', $this->filter['id']); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Title']?></label>
                <?=cot_inputbox('text', 'f[title]', $this->filter['title']); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Enabled']?></label>
                <?=cot_selectbox($this->filter['active'], 'f[active]', array_keys($this->states), array_values($this->states), false); ?>
            </div>

            <button type="submit" class="btn btn-default"><span class="fa fa-filter"></span> <?=cot::$L['Show']?></button>

            <a href="<?=cot_url('admin', array('m'=>'subscribe'))?>" class="btn btn-default"><span class="fa fa-remove"></span></a>
        </form>
    </div>
</div>
<?php

// Список рассылок ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <?php if(!empty($items)) { ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?=cot::$L['Title']?></th>
                    <th><?=cot::$L['subscribe_from_title']?></th>
                    <th><?=cot::$L['subscribe_schedule']?></th>
                    <th><?=cot::$L['subscribe_next_run']?></th>
                    <th><?=cot::$L['subscribe_last_executed']?></th>
                    <th><?=cot::$L['Status']?></th>
                    <th>ID</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = $this->fistNumber;
                foreach ($items as $itemRow) {
                    $itemUrlParams = array(
                        'm' => 'subscribe',
                        'a' => 'edit',
                        'id' => $itemRow->id,
                    );
                    $itemUrl = cot_url('admin', $itemUrlParams);
                    ?>
                    <tr>
                        <td><?=$i?></td>
                        <td class="strong">
                            <a href="<?=$itemUrl?>"><?=htmlspecialchars($itemRow->title)?></a>
                        </td>
                        <td><?=htmlspecialchars($itemRow->from_title)?></td>
                        <td>
                            <?php if(!empty($itemRow->sched_mday) || !empty($itemRow->sched_wday) || !empty($itemRow->sched_time)) {
                                if(!empty($itemRow->sched_mday)) { ?>
                                    <div><?=cot::$L['subscribe_mday']?>: <?=$itemRow->sched_mday?></div>
                                <?php }

                                if(!empty($itemRow->sched_wday)) { ?>
                                    <div><?=cot::$L['subscribe_wday']?>: <?=$itemRow->sched_mday?></div>
                                <?php }

                                if(!empty($itemRow->sched_time)) { ?>
                                    <div><span class="fa fa-clock-o"></span> <?=$itemRow->sched_time?></div>
                                <?php }
                            } else { ?>
                                <div class="text-center"><span class="fa fa-minus"></span></div>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $nextRun = '<div class="text-center"><span class="fa fa-minus"></span></div>';
                            if(!empty($itemRow->next_run)) {
                                $tmp = strtotime('1970-01-02 00:01:00');
                                $tmp2 = strtotime($itemRow->next_run);
                                if ($tmp2 > $tmp) $nextRun = cot_date($Ldt['datetime_full'], $tmp2);
                            }
                            echo $nextRun;
                            ?>
                        </td>
                        <td>
                            <?php
                            $lastEx = '<span class="fa fa-minus"></span>';
                            if(!empty($itemRow->last_executed)) {
                                $tmp = strtotime('1970-01-02 00:01:00');
                                $tmp2 = strtotime($itemRow->last_executed);
                                if ($tmp2 > $tmp) $lastEx = cot_date($Ldt['datetime_full'], $tmp2);
                            }
                            ?>
                            <?=$lastEx?>
                            <br /><?=cot::$L['subscribe_mails']?>: <?=$itemRow->last_sent?>
                        </td>
                        <td>
                            <?php if($itemRow->running) { ?>
                                <span class="label label-primary"><?=cot::$L['subscribe_running']?></span><br />
                            <?php }

                            if($itemRow->active) { ?>
                                <span class="label label-success"><?=cot::$L['Enabled']?></span>
                            <?php } else { ?>
                                <span class="label label-default"><?=cot::$L['Disabled']?></span>
                            <?php }

                            if($itemRow->periodical) { ?>
                                <br /><span class="label label-info"><?=cot::$L['subscribe_periodical']?></span>
                            <?php } ?>
                        </td>
                        <td><?=$itemRow->id?></td>
                        <td>
                            <?php
                            $tmp = array(
                                'm' => 'subscribe',
                                'a' => 'edit',
                                'id' => $itemRow->id,
                            );
                            ?>
                            <div class="text-right">
                                <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'view', 'id'=>$itemRow->id))
                                ?>" class="btn btn-xs btn-default" title="<?=cot::$L['Preview']?>" data-toggle="tooltip">
                                    <span class="fa fa-eye"></span></a>

                                <a href="<?=cot_url('admin', array('m'=>'subscribe', 'n'=>'user', 'f[sid]'=>$itemRow->id))
                                    ?>" class="btn btn-xs btn-info" title="<?=cot::$L['subscribe_subscribers']?>" data-toggle="tooltip">
                                    <span class="fa fa-users"></span></a>

                                <a href="<?=cot_url('admin', $tmp)?>" class="btn btn-xs btn-default"
                                   title="<?=cot::$L['Edit']?>" data-toggle="tooltip">
                                    <span class="fa fa-edit"></span></a>
                                <?php

                                /*
                                $tmp['act'] = 'clone';
                                ?>
                                <a href="<?=cot_url('admin', $tmp)?>" class="btn btn-xs btn-default marginbottom10">
                                    <span class="fa fa-copy"></span> <?=cot::$L['catalog_clone']?></a>
                                */
                                $delUrlParams = $this->urlParams;
                                $delUrlParams['a']= 'delete';
                                $delUrlParams['id'] = $itemRow->id;
                                if(!empty($this->pagenav['page'])) $delUrlParams['d'] = $this->pagenav['page'];
                                ?>
                                <a href="<?=cot_confirm_url(cot_url('admin', $delUrlParams, '', true), 'subscribe',
                                    'subscribe_delete_confirm')?>" class="btn btn-xs btn-danger confirmLink"
                                   title="<?= cot::$L['Delete'] ?>" data-toggle="tooltip">
                                    <span class="fa fa-trash-o"></span></a>
                            </div>
                        </td>
                    </tr>
                <?php
                    $i++;
                } ?>
                </tbody>
            </table>

            <?php if(!empty($this->pagenav['main'])) { ?>
                <div class="text-right">
                    <nav>
                        <ul class="pagination" style="margin: 0"><?=$this->pagenav['prev']?><?=$this->pagenav['main']?><?=$this->pagenav['next']?></ul>
                    </nav>
                    <span class="help-block">
                        <?=cot::$L['Total']?>: <?=$this->pagenav['entries']?>, <?=cot::$L['Onpage']?>: <?=$this->pagenav['onpage']?>
                    </span>
                </div>
            <?php }

        } else { ?>
            <h4 class="text-muted text-center"><?=cot::$L['None']?></h4>
        <?php } ?>
    </div>
</div>
