<?php
/**
 * Subscribe module for Cotonti Siena
 *
 * Subscribes list template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

global $Ldt;

/** @var subscribe_model_Subscribe[] $subscribes рассылки */
$items = $this->items;

/** @var subscribe_model_Subscriber[] $subscribers подписки пользователя */
//$subscribers = $this->subscribers;

echo $this->breadcrumbs;
?>
<h1><?=$this->page_title?></h1>
<?php

// Error and message handling
$this->displayMessages();

if(!empty($items)) {
    if(cot::$usr['id'] > 0) echo cot_xp();

    foreach($items as $itemRow) {
        $nextRun = '';
        if(!empty($itemRow->next_run)) {
            $tmp = cot::$sys['now'];
            $tmp2 = strtotime($itemRow->next_run);
            if ($tmp2 > $tmp) $nextRun = cot_date($Ldt['datetime_full'], $tmp2);
        }
        ?>
        <div class="list-row subscribe">
            <h2>
                <?=htmlspecialchars($itemRow->title)?>
            </h2>

            <?php
            // Статус рассылки
            if($itemRow->periodical) { ?>
            <div>
                <span class="label label-info"><?=cot::$L['subscribe_periodical']?></span>
            </div>
            <?php }

            if(!empty($itemRow->description)) { ?>
            <div><?=$itemRow->description?></div>
            <?php }

            // Статус подписки текущего пользователя
            if(cot::$usr['id'] > 0) { ?>
                <div class="margintop10">
                    <?php if(in_array($itemRow->id, $this->userSubscribesIds)) { ?>
                        <span class="label label-success"><?=cot::$L['subscribe_you_subscribed']?></span>
                        <button class="btn btn-danger btn-sm subscribe-toggle" data-id="<?=$itemRow->id?>"><?=cot::$L['subscribe_unsubscribe']?></button>
                    <?php } else { ?>
                        <button class="btn btn-default btn-sm subscribe-toggle" data-id="<?=$itemRow->id?>"><?=cot::$L['subscribe_to_subscribe']?></button>
                    <?php } ?>
                </div>
            <?php } ?>
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
