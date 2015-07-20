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

/** @var subscribe_model_Queue[] $items */
$items = $this->items;

// Фильтры для модератора ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=cot::$L['Filters']?></div>
    <div class="panel-body">
        <form method="get" action="<?=(cot_url('admin', array('m'=>'subscribe')))?>" class="form-inline">
            <?=$this->filterForm['hidden']?>

            <div class="form-group">
                <label><?=cot::$L['subscribe_subscribe']?></label>
                <?=cot_selectbox($this->filter['sid'], 'f[sid]', array_keys($this->subscribes), array_values($this->subscribes)); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Name']?></label>
                <?=cot_inputbox('text', 'f[to_name]', $this->filter['to_name']); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Email']?></label>
                <?=cot_inputbox('text', 'f[to_name]', $this->filter['to_name']); ?>
            </div>

            <button type="submit" class="btn btn-default"><span class="fa fa-filter"></span> <?=cot::$L['Submit']?></button>

            <a href="<?=cot_url('admin', array('m'=>'subscribe', 'n'=>'queue'))?>" class="btn btn-default"><span class="fa fa-remove"></span></a>
        </form>
    </div>
</div>
<?php

// Список подписчиков ?>
<div class="panel panel-default margintop10">
    <div class="panel-heading"><?=$this->page_title?></div>
    <div class="panel-body">
        <?php if(!empty($items)) { ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?=cot::$L['Email']?></th>
                    <th><?=cot::$L['Name']?></th>
                    <th><?=cot::$L['subscribe_subscribe']?></th>
                    <th><?=cot::$L['Added']?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $i = $this->fistNumber;
                foreach ($items as $itemRow) {
                    ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td class="strong"><?=htmlspecialchars($itemRow->to_email) ?></td>
                        <td><?=htmlspecialchars($itemRow->to_name) ?></td>
                        <td>
                            <?php
                            $tmp = $itemRow->rawValue('subscribe');
                            if(array_key_exists($tmp, $this->subscribes)) { ?>
                                <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'edit', 'id'=>$tmp))?>"><?=htmlspecialchars($this->subscribes[$tmp])?></a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $created = '<span class="fa fa-minus"></span>';
                            if (!empty($itemRow->created)) {
                                $created = cot_date($Ldt['datetime_full'], strtotime($itemRow->created));
                            }
                            echo $created;
                            ?>
                        </td>
                        <td>
                            <div class="text-right">
                                <?php
                                $delUrlParams = $this->urlParams;
                                $delUrlParams['a'] = 'delete';
                                $delUrlParams['id'] = $itemRow->id;
                                if (!empty($this->pagenav['page'])) $delUrlParams['d'] = $this->pagenav['page'];
                                ?>
                                <a href="<?= cot_confirm_url(cot_url('admin', $delUrlParams, '', true)) ?>" class="btn btn-xs btn-danger confirmLink"
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

            <?php if (!empty($this->pagenav['main'])) { ?>
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
