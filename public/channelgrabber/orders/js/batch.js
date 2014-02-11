define(function() {
    return function(notifications, batchSelector, batchCgMustache) {
        var notifications = notifications;
        var selector = batchSelector;
        var cgMustache = batchCgMustache;
        var template;
        var mustacheInstance;
        var that = this;

        this.action = function(event) {
            var datatable = $(this).data("datatable");
            if (!datatable) {
                return;
            }

            var orders = $("#" + datatable).cgDataTable("selected", ".order-id");
            if (!orders.length) {
                return;
            }

            notifications.notice('Adding orders to a batch');
            $.ajax({
                url: $(this).data("url"),
                type: "POST",
                dataType: 'json',
                data: {
                    'orders': orders
                },
                success : function(data) {
                    notifications.success("Orders successfully batched");
                    that.redraw();
                    if (datatable) {
                        $("#" + datatable).cgDataTable("redraw");
                    }
                },
                error: function (error, textStatus, errorThrown) {
                    return notifications.ajaxError(error, textStatus, errorThrown);
                }
            });
        };

        this.redraw = function () {
            $.ajax({
                url: $(selector).attr("data-url"),
                type: "GET",
                dataType: 'json',
                success : function(data) {
                    $(selector).html("");
                    $.each(data, function(index) {
                        $(selector).append(mustacheInstance.renderTemplate(template, data[index]));
                    });
                }
            });
        }

        var templateUrl = $(selector).attr('data-mustacheTemplate');
        cgMustache.get().fetchTemplate(templateUrl, function(batchTemplate, batchMustacheInstance) {
            template = batchTemplate;
            mustacheInstance = batchMustacheInstance;
        });
    };
});
