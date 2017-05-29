$(function() {
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
});