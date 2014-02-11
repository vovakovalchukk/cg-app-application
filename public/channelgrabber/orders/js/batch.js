define(function() {
    var Batch = function(notifications, selector, cgMustache) {
        var template;
        var mustacheInstance;

        cgMustache.get().fetchTemplate($(selector).attr('data-mustacheTemplate'),
            function(batchTemplate, batchMustacheInstance) {
                template = batchTemplate;
                mustacheInstance = batchMustacheInstance;
        });

        this.getNotifications = function() {
            return notifications;
        };

        this.getSelector = function() {
            return selector;
        };

        this.getTemplate = function() {
            return template;
        };

        this.getMustacheInstance = function() {
            return mustacheInstance;
        };
    };

    Batch.prototype.action = function(element) {
        var datatable = $(element).data('datatable');
        if (!datatable) {
            return;
        }

        var orders = $('#' + datatable).cgDataTable('selected', '.order-id');
        if (!orders.length) {
            return;
        }

        this.getNotifications().notice('Adding orders to a batch');
        $.ajax({
            url: $(element).data('url'),
            type: 'POST',
            dataType: 'json',
            data: {'orders': orders},
            context: this,
            success : this.actionSuccess,
            error: function (error, textStatus, errorThrown) {
                return notifications.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    Batch.prototype.actionSuccess = function(data) {
        this.getNotifications().success('Orders successfully batched');
        this.redraw();
        if (datatable) {
            $('#' + datatable).cgDataTable('redraw');
        }
    };

    Batch.prototype.redraw = function() {
        $.ajax({
            url: $(this.getSelector()).attr('data-url'),
            type: 'GET',
            dataType: 'json',
            context: this,
            success : this.redrawSuccess
        });
    };

    Batch.prototype.redrawSuccess = function(data) {
        var that = this;
        $(that.getSelector()).html('');
        $.each(data, function(index) {
            $(that.getSelector()).append(that.getMustacheInstance().renderTemplate(template, data[index]));
        });
    };

    return Batch;
});
