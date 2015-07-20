/**
 * Subscribe module for Cotonti Siena
 *
 * @package Subscribe
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */

$('#subscribeFormModal').on('show.bs.modal', function (event) {
    // Button that triggered the modal
    var button = $(event.relatedTarget);
    var act = button.data('whatever');
    var modal = $(this);
    var subscribeForm = $('#subscribeForm');

    if(act == 'addSubscriber') {
        // Очистить форму редактирования
        subscribeForm.find('input[name="subrid"]').val('0');
        subscribeForm.find('input[type="text"]').val('');
        subscribeForm.find('select').val(0);
        $('#subscribeFormError').hide();
        $('#subscribeFormSubmit').removeAttr('disabled');
        $('#subscribeForm').css('opacity', 1);
        $('#loading').remove();


        modal.find('.modal-title').text(subscribeLang.addNewSubscriber);
    }
});

$('#subscribeFormSubmit').click(function(e){
    e.preventDefault();

    var me = $(this);
    var rData = $('#subscribeForm').serialize();

    var bgspanParent = me.parents('#subscribeForm');
    var bgspan = $('<span>', {
        id: "loading",
        class: "loading"
    })  .css({'position': 'absolute', 'left': "50%", top: "50%", 'margin-left': '-110px', 'margin-top': '-9px'});
    bgspan.html('<img src="./images/spinner.gif" alt="loading"/>');
    bgspanParent.append(bgspan).css('position', 'relative').css('opacity', 0.4);

    me.attr('disabled', 'disabled');

    var jqxhr = $.post( "admin.php?m=subscribe&n=user&a=ajxEdit", rData, function(data) {
        $('#subscribeFormSubmit').removeAttr('disabled');
        bgspanParent.css('opacity', 1);
        $('#loading').remove();

        if(data.error != ''){
            $('#subscribeFormError').html(data.error).slideDown();

        }else {
            $('#subscribeFormError').hide();
            window.location.reload();
        }
    }, 'json').fail(function() {
        alert( "Error. Try again later." );
        $('#subscribeFormSubmit').removeAttr('disabled');
        bgspanParent.css('opacity', 1);
        $('#loading').remove();
    });
});

$('.subscribe-enable').click(function(e){
    e.preventDefault();

    var me = $(this);
    var id = me.attr('data-id');
    var x = $('input[name="x"]').val();

    var bgspanParent = me.closest('div');
    var bgspan = $('<span>', {
        id: "loading",
        class: "loading"
    })  .css({'position': 'absolute', 'left': "50%", top: "50%", 'margin-left': '-110px', 'margin-top': '-9px'});
    bgspan.html('<img src="./images/spinner.gif" alt="loading"/>');
    bgspanParent.append(bgspan).css('position', 'relative').css('opacity', 0.4);

    var jqxhr = $.post( "admin.php?m=subscribe&n=user&a=ajxEnable", {id: id, x: x}, function(data) {
        bgspanParent.css('opacity', 1);
        $('#loading').remove();

        if(data.error != ''){
            alert(data.error);

        }else {
            window.location.reload();
        }
    }, 'json').fail(function() {
        alert( "Error. Try again later." );
        bgspanParent.css('opacity', 1);
        $('#loading').remove();
    });
});