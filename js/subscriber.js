/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */


if (window.subscribeWidgetShowError === undefined) {
    var subscribeWidgetShowError = function(msg, id, msgType) {
        msgType = msgType || 'message';

        var msgClass = 'alert alert-success subscribe-me-message',
            msgId = "subscribe-me-message-" + id;
        if(msgType == 'error'){
            msgClass = 'alert alert-danger subscribe-me-message';
            msgId = "subscribe-me-message-" + id;
        }

        var container = $('<div>', {
            id: msgId,
            class: msgClass
            }).html(msg);

        $('#subscribe-me-' + id).append(container);
    }
}

$( document ).on( "click", ".subscribe-me-submit", function(e){
    e.preventDefault();

    var me = $(this),
        x = $('input[name="x"]').val(),
        parent = me.closest('.subscribe-me');

    var id = parent.attr('id');
    id = id.replace('subscribe-me-', '');

    var email = parent.find('[name="email"]').val();

    if(email == '') {
        parent.addClass('has-error');
        return false;
    }

    $('#subscribe-me-message-' + id).remove();
    parent.removeClass('has-error');

    var bgspanParent = parent;
    var bgspan = $('<span>', {
        id: "loading",
        class: "loading"
    })  .css({'position': 'absolute', 'left': "50%", top: "50%", 'margin-left': '-110px', 'margin-top': '-9px'});
    bgspan.html('<img src="./images/spinner.gif" alt="loading"/>');
    bgspanParent.append(bgspan).css('position', 'relative').css('opacity', 0.4);

    me.attr('disabled', 'disabled');

    var jqxhr = $.post( "index.php?e=subscribe&m=user&a=ajxSubscribe", {id: id, email: email, x: x}, function(data) {
        me.removeAttr('disabled');
        bgspanParent.css('opacity', 1);
        bgspan.remove();

        if(data.error != ''){
            subscribeWidgetShowError(data.error, id, 'error');

        }else {
            data.message = data.message || '';
            subscribeWidgetShowError(data.message, id);
        }
    }, 'json').fail(function() {

        subscribeWidgetShowError('Error. Try again later.', id, 'error');
        me.removeAttr('disabled');
        bgspanParent.css('opacity', 1);
        bgspan.remove();
    });
});

$( document ).on( "click", ".subscribe-toggle", function(e) {
    e.preventDefault();

    var me = $(this),
        parent = me.closest('.subscribe'),
        params = {
            x: $('input[name="x"]').val(),
            id: me.attr('data-id')
        },
        uid = me.attr('data-uid');

    uid = parseInt(uid);

    if(uid > 0)  params['uid'] = uid;

    var bgspan = $('<span>', {
        id: "loading",
        class: "loading"
    })  .css({'position': 'absolute', 'left': "50%", top: "50%", 'margin-left': '-110px', 'margin-top': '-9px'});
    bgspan.html('<img src="./images/spinner.gif" alt="loading"/>');
    parent.append(bgspan).css('position', 'relative').css('opacity', 0.4);

    me.attr('disabled', 'disabled');

    var jqxhr = $.post( "index.php?e=subscribe&m=user&a=ajxSubscribeToggle", params, function(data) {
        if(data.error != ''){
            alert(data.error);

            me.removeAttr('disabled');
            parent.css('opacity', 1);
            bgspan.remove();

        }else {
            window.location.reload();
        }
    }, 'json').fail(function() {

        alert('Error. Try again later.');
        me.removeAttr('disabled');
        parent.css('opacity', 1);
        bgspan.remove();
    });


});