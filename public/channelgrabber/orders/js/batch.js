define([
    'element/ElementCollection'
], function(
    elementCollection
) {
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

        this.getElementCollection = function()
        {
            return elementCollection;
        };
    };

    Batch.prototype.action = function(element) {
        this.datatable = $(element).data('datatable');
        if (!this.datatable) {
            return;
        }

        var ajax = {
            url: $(element).data('url'),
            type: 'POST',
            dataType: 'json',
            context: this,
            success : this.actionSuccess,
            error: function (error, textStatus, errorThrown) {
                return this.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        };

        if ($("#" + this.datatable + "-select-all").is(":checked")) {
            ajax.url += "/" + $('#' + this.datatable).data("filterId");
        } else {
            var orders = $('#' + this.datatable).cgDataTable('selected', '.checkbox-id');
            if (!orders.length) {
                return;
            }
            ajax.data = {
                orders: orders
            };
        }

        this.getNotifications().notice('Adding orders to a batch');
        $.ajax(ajax);
    };

    Batch.prototype.actionSuccess = function(data) {

        this.getNotifications().success('Orders successfully batched');
        this.redraw();
        $('#' + this.datatable).cgDataTable('redraw');
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
        var self = this;
        var batchOptions = [];
        $(self.getSelector()).html('');
        $.each(data['batches'], function(index) {
            $(self.getSelector()).append(self.getMustacheInstance().renderTemplate(self.getTemplate(), data['batches'][index]));
            batchOptions.push({
                title: data['batches'][index].name,
                value: data['batches'][index].name
            });
        });

        $(document).trigger('filterable-options-changed', ['batch', batchOptions]);
    };

    Batch.prototype.remove = function(element) {
        this.datatable = $(element).data('datatable');
        if (!this.datatable) {
            return;
        }

        var ajax = {
            url: $(element).data('url'),
            type: 'POST',
            dataType: 'json',
            context: this,
            success : this.removeSuccess,
            error: function (error, textStatus, errorThrown) {
                return this.getNotifications().ajaxError(error, textStatus, errorThrown);
            }
        };

        if ($("#" + this.datatable + "-select-all").is(":checked")) {
            ajax.url += "/" + $('#' + this.datatable).data("filterId");
        } else {
            var orders = $('#' + this.datatable).cgDataTable('selected', '.checkbox-id');
            if (!orders.length) {
                return;
            }
            ajax.data = {
                orders: orders
            };
        }

        this.getNotifications().notice('Removing orders from batch');
        $.ajax(ajax);
    };

    Batch.prototype.removeSuccess = function(data) {
        this.getNotifications().success('Orders removed from batched');
        $('#' + this.datatable).cgDataTable('redraw');
    };

    return Batch;
});
