<?php
/**
 * Subscribe widget form template
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright (c) Portal30 Studio http://portal30.ru
 */

/** @var subscribe_model_Subscribe $subscribe */
$subscribe = $this->subscribe;

if(!empty($subscribe)) {
?>
<div id="subscribe-me-<?=$subscribe->id?>" class="subscribe-me">
    <?=cot_xp()?>
    <div class="input-group marginbottom10">
        <input type="text" name="email" class="form-control" placeholder="<?=cot::$L['Email']?> ...">
        <span class="input-group-btn">
            <button class="btn btn-default subscribe-me-submit" type="button"><?=cot::$L['subscribe_to_subscribe']?></button>
        </span>
    </div>
</div>
<?php }