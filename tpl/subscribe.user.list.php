<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * User subscribes list template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

global $Ldt;

/** @var subscribe_model_Subscribe[] $subscribes рассылки */
$subscribes = $this->subscribes;

/** @var subscribe_model_Subscriber[] $userSubscribes подписки пользователя */
$userSubscribes = $this->userSubscribes;

echo $this->breadcrumbs;
?>
<h1><?=$this->page_title?></h1>
<?php

// Error and message handling
$this->displayMessages();

if(!empty($userSubscribes)) {

    foreach($userSubscribes as $itemRow) {
        $subRow = $subscribes[$itemRow->rawValue('subscribe')];
        $nextRun = '';

        $attrs = 'data-id="'.$itemRow->rawValue('subscribe').'"';
        if($this->user['user_id'] != cot::$usr['id']) $attrs .= ' data-uid="'.$this->user['user_id'].'"';

        if(!empty($subRow->next_run)) {
            $tmp = cot::$sys['now'];
            $tmp2 = strtotime($subRow->next_run);
            if ($tmp2 > $tmp) $nextRun = cot_date($Ldt['datetime_full'], $tmp2);
        }
        ?>
        <div class="list-row subscribe">
            <h2>
                <?=htmlspecialchars($subRow->title)?>
            </h2>
            <div>
                <?php
                // Статус рассылки
                if(!$subRow->active) {
                    echo cot::$L['subscribe_subscribe'].': ';?>
                    <span class="label label-default"><?=cot::$L['Disabled']?></span>
                <?php }

                if($subRow->periodical) { ?>
                    <span class="label label-info"><?=cot::$L['subscribe_periodical']?></span>
                <?php }

                if(!empty($nextRun)) {
                    //echo cot::$L['subscribe_next_run'].': '.$nextRun;
                } ?>
            </div>
            <?php

            if(!empty($subRow->description)) { ?>
            <div class="margintop10"><?=$subRow->description?></div>
            <?php }

            // Статус подписки
            ?>
            <div class="margintop10">
                <?php if($itemRow->active) { ?>
                    <span class="label label-success"><?=cot::$L['subscribe_you_subscribed']?></span>
                    <button class="btn btn-danger btn-sm subscribe-toggle" <?=$attrs?>><?=cot::$L['subscribe_unsubscribe']?></button>
                <?php } else { ?>
                    <button class="btn btn-default btn-sm subscribe-toggle" <?=$attrs?>><?=cot::$L['subscribe_to_subscribe']?></button>
                <?php }

                if (!$itemRow->email_valid) { ?>
                    <span class="label label-danger"><?= cot::$L['subscribe_email_not_validated'] ?></span>
                <?php }
                ?>
            </div>
        </div>
    <?php
    }

        if(!empty($this->pagenav['main'])) { ?>
        <div class="text-right">
            <nav>
                <ul class="pagination" style="margin: 0"><?=$this->pagenav['prev']?><?=$this->pagenav['main']?><?=$this->pagenav['next']?></ul>
            </nav>
        </div>
    <?php }

} else { ?>
    <h4 class="text-muted text-center"><?=cot::$L['None']?></h4>
<?php } ?>
