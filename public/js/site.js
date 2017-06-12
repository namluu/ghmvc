$(function() {
    /**
     * Event click notification notice
     * */
    $('.navbar ').on('click', '#noti-bell', function() {
        var self = this,
            $unreadNotification = $(self).find('.label-danger');

        // only call ajax when login + has unread notification for performance
        if (global_variables.current_user_id && $unreadNotification.length) {
            $.ajax({
                url: global_variables.site_url + 'user/account/readNotification',
                type: 'post',
                data: {
                    user_id: global_variables.current_user_id
                },
                dataType: 'json'
            }).done(function () {
                //console.log('success');
                $unreadNotification.remove();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                //console.log('error');
                console.log(errorThrown);
            }).always(function () {
                //console.log('complete');
            });
        }
    });
    /**
     * Hide reply form by default - show when click Reply
     * */
    $('.comment').on('click', '.item-reply a', function (e) {
        e.preventDefault();
        var $form = $(this).parent().find('.form-post-reply');
        if ($form.length) {
            $form.show();
        }
    });
    /**
     * Submit reply when enter
     * */
    $('.comment').on('keydown', '.control-reply', function (e) {
        if (this.value == '' && e.keyCode == 13) {
            e.preventDefault();
        }
    });
    /**
     * Read more reply
     * */
    $('.reply-more').on('click', function(e) {
        e.preventDefault();
        $(this).hide().siblings('.media').show();
    });
});