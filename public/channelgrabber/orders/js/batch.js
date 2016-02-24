define([
    'Orders/OrdersBulkActionAbstract',
    'element/ElementCollection',
    'Orders/SaveCheckboxes',
    'cg-mustache'
], function(
    OrdersBulkActionAbstract,
    elementCollection,
    saveCheckboxes,
    CGMustache
) {
    var Batch = function(selector)
    {
        OrdersBulkActionAbstract.call(this);

        var template;
        var mustacheInstance;

        CGMustache.get().fetchTemplate($(selector).attr('data-mustacheTemplate'),
            function(batchTemplate, batchMustacheInstance) {
                template = batchTemplate;
                mustacheInstance = batchMustacheInstance;
        });

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

        this.getSaveCheckboxes = function()
        {
            return saveCheckboxes;
        };
    };

    Batch.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    Batch.prototype.invoke = function()
    {
        var datatable = this.getDataTableElement();
        var orders = this.getOrders();
        if (!datatable.length || !orders.length) {
            return;
        }
        if (this.getElement().data('action') == 'remove') {
            return this.remove();
        }

        var ajax = {
            url: this.getElement().data('url'),
            type: 'POST',
            dataType: 'json',
            data: this.getDataToSubmit(),
            context: this,
            success : this.actionSuccess,
            error: function (error, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        };

        this.getNotificationHandler().notice('Adding orders to a batch');
        $.ajax(ajax);
        this.getSaveCheckboxes().setSavedCheckboxes(orders)
            .setSavedCheckAll(this.isAllSelected());
    };

    Batch.prototype.actionSuccess = function(data)
    {
        this.getNotificationHandler().success('Orders successfully batched');
        this.setFilterId(data.filterId);
        this.redraw();
        this.getDataTableElement().cgDataTable('redraw');
        this.getSaveCheckboxes().refreshCheckboxes(this.getDataTableElement());
    };

    Batch.prototype.redraw = function()
    {
        $.ajax({
            url: $(this.getSelector()).attr('data-url'),
            type: 'GET',
            dataType: 'json',
            context: this,
            success : this.redrawSuccess
        });
    };

    Batch.prototype.redrawSuccess = function(data)
    {
        var self = this;
        var batchOptions = [];
        $(self.getSelector()).html('');
        $.each(data['batches'], function(index)
        {
            var batch = data['batches'][index];
            if (batch.active) {
                $(self.getSelector()).append(self.getMustacheInstance().renderTemplate(self.getTemplate(), batch));
            }
            batchOptions.push({
                title: batch.name,
                value: batch.name + ""
            });
        });

        $(document).trigger('filterable-options-changed', ['batch', batchOptions]);
    };

    Batch.prototype.remove = function(element)
    {
        var ajax = {
            url: this.getElement().data('url'),
            type: 'POST',
            dataType: 'json',
            data: this.getDataToSubmit(),
            context: this,
            success : this.removeSuccess,
            error: function (error, textStatus, errorThrown) {
                return this.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        };

        this.getNotificationHandler().notice('Removing orders from batch');
        $.ajax(ajax);

        this.getSaveCheckboxes().setSavedCheckboxes(this.getOrders())
            .setSavedCheckAll(this.isAllSelected());
    };

    Batch.prototype.removeSuccess = function(data)
    {
        this.getNotificationHandler().success('Orders removed from batched');
        this.setFilterId(data.filterId);
        this.getDataTableElement().cgDataTable('redraw');
        this.getSaveCheckboxes().refreshCheckboxes(this.getDataTableElement());
    };

    return Batch;
});
