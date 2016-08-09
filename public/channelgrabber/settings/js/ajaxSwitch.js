define(
    ["ajaxCheckbox", "mustache"],
    function(AjaxCheckbox, Mustache) {
        return function(notifications, baseSelector, selector, ajaxOptions, messages, preventAjaxRequest) {
            var ajaxCheckbox = new AjaxCheckbox(notifications, baseSelector, selector, ajaxOptions, preventAjaxRequest);

            ajaxCheckbox.bindAjax(function() {
                if (messages.info !== undefined) {
                    ajaxCheckbox.getNotifications().notice(messages.info);
                }
            });

            ajaxCheckbox.bindAjax(function(event, ajaxOptions) {
                var id = $(this).data("id");
                var type = $(this).data("type");
                if (!id || !type) {
                    return;
                }
                ajaxOptions.url = Mustache.render(ajaxOptions.url, {'id': id, 'type': type});
            });

            ajaxCheckbox.bindAjaxError(function() {
                $(this).prop("checked", !$(this).is(":checked"));
            });

            ajaxCheckbox.bindAjaxResponse(function(event, data) {
                var notifications = ajaxCheckbox.getNotifications();

                if (!data.updated) {
                    if (messages.error) {
                        notifications.error(messages.error);
                    }
                    return;
                }

                if (messages.success) {
                    notifications.success(messages.success);
                }

                $(baseSelector + ' ' + selector).trigger('saved');
            });

            return ajaxCheckbox;
        };
    }
);