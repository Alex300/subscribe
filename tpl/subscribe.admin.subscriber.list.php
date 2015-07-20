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

/** @var subscribe_model_Subscriber[] $items */
$items = $this->items;

// Add new adv and edit category buttons
if(count($this->subscribes) > 0) { ?>
    <div class="text-right">
        <a href="#" class="btn btn-primary btn-sm addSubscriber" data-toggle="modal" data-target="#subscribeFormModal"
           data-whatever="addSubscriber"><span class="fa fa-plus"></span> <?= cot::$L['subscribe_add_new_subscriber'] ?>
        </a>
    </div>
<?php }

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
                <label><?=cot::$L['subscribe_subscribe']?></label>
                <?=cot_selectbox($this->filter['sid'], 'f[sid]', array_keys($this->subscribes), array_values($this->subscribes)); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Name']?></label>
                <?=cot_inputbox('text', 'f[name]', $this->filter['name']); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Email']?></label>
                <?=cot_inputbox('text', 'f[email]', $this->filter['email']); ?>
            </div>

            <div class="form-group">
                <label><?=cot::$L['Enabled']?></label>
                <?=cot_selectbox($this->filter['active'], 'f[active]', array_keys($this->states), array_values($this->states), false); ?>
            </div>

            <button type="submit" class="btn btn-default"><span class="fa fa-filter"></span> <?=cot::$L['Submit']?></button>

            <a href="<?=cot_url('admin', array('m'=>'subscribe', 'n'=>'user'))?>" class="btn btn-default"><span class="fa fa-remove"></span></a>
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
                    <th><?=cot::$L['subscribe_last_sent_time']?></th>
                    <th><?=cot::$L['Enabled']?></th>
                    <th><?=cot::$L['subscribe_unsubscr_code']?></th>
                    <th><?=cot::$L['subscribe_subscribed']?></th>
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
                        <td><?= $i ?></td>
                        <td class="strong"><?=htmlspecialchars($itemRow->email) ?></td>
                        <td><?=htmlspecialchars($itemRow->name) ?></td>
                        <td>
                            <?php
                            $tmp = $itemRow->rawValue('subscribe');
                            if(array_key_exists($tmp, $this->subscribes)) { ?>
                                <a href="<?=cot_url('admin', array('m'=>'subscribe', 'a'=>'edit', 'id'=>$tmp))?>"><?=htmlspecialchars($this->subscribes[$tmp])?></a>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $lastEx = '<span class="fa fa-minus"></span>';
                            if (!empty($itemRow->last_executed)) {
                                $tmp = strtotime('1970-01-02 00:01:00');
                                $tmp2 = strtotime($itemRow->last_executed);
                                if ($tmp2 > $tmp) $lastEx = cot_date($Ldt['datetime_full'], $tmp2);
                            }
                            echo $lastEx;
                            ?>
                        </td>
                        <td>
                            <div>
                                <?php if ($itemRow->active) { ?>
                                    <button class="btn btn-success btn-xs subscribe-enable" data-id="<?=$itemRow->id?>"><?= cot::$L['Enabled'] ?></button><br />
                                <?php } else { ?>
                                    <button class="btn btn-warning btn-xs subscribe-enable" data-id="<?=$itemRow->id?>"><?= cot::$L['Disabled'] ?></button><br />
                                <?php }

                                if ($itemRow->email_valid) { ?>
                                    <span class="label label-success"><?= cot::$L['subscribe_email_validated'] ?></span>
                                <?php } else { ?>
                                    <span class="label label-danger"><?= cot::$L['subscribe_email_not_validated'] ?></span>
                                <?php }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?=$itemRow->unsubscr_code  ?>
                        </td>
                        <td>
                            <?php
                            $created = '<span class="fa fa-minus"></span>';
                            if(!empty($itemRow->created)) {
                                $tmp = strtotime('1970-01-02 00:01:00');
                                $tmp2 = strtotime($itemRow->created);
                                if ($tmp2 > $tmp) $created = cot_date($Ldt['datetime_full'], $tmp2);
                            }
                            echo $created;
                            ?>
                            <br /><?=$itemRow->ip?>
                        </td>
                        <td><?= $itemRow->id ?></td>
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
<?php

// Форма подписки ?>
<div class="modal fade" id="subscribeFormModal" tabindex="-1" role="dialog" aria-labelledby="subscribeFormModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="subscribeFormModalLabel"><?=cot::$L['subscribe_add_new_subscriber']?></h4>
            </div>
            <form id="subscribeForm" method="post">
                <?=cot_inputbox('hidden', 'subrid', 0)?>
                <div class="modal-body">
                    <div id="subscribeFormError" class="alert alert-danger" role="alert" style="display: none;"></div>

                    <div class="form-group">
                        <label><?=cot::$L['subscribe_subscribe']?></label>
                        <?=cot_selectbox($this->filter['sid'], 'subscribe', array_keys($this->subscribes), array_values($this->subscribes),
                            true, array('id' => 'subscribeForm_subscribe'))?>
                    </div>
                    <div class="form-group">
                        <label><?=cot::$L['Email']?></label>
                        <?=cot_inputbox('text', 'email', '', array('id' => 'subscribeForm_email'))?>
                    </div>
                    <div class="form-group">
                        <label><?=cot::$L['Name']?></label>
                        <?=cot_inputbox('text', 'name', '', array('id' => 'subscribeForm_name'))?>
                    </div>
                    <div class="form-group">
                        <label><?=cot::$L['User']?> (ID)</label>
                        <?=cot_inputbox('text', 'user', '', array('id' => 'subscribeForm_user'))?>
                    </div>
                    <div class="checkbox">
                        <?=cot_checkbox(false, 'active', cot::$L['Enabled'], array('id' => 'subscribeForm_active'))?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="subscribeFormSubmit" class="btn btn-primary"><?=cot::$L['Submit']?></button>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=cot::$L['Close']?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    var subscribeLang = {
        addNewSubscriber: '<?=cot::$L['subscribe_add_new_subscriber']?>'
    };
</script>