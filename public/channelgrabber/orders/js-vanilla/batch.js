define([
    'Orders/OrdersBulkActionAbstract',
    'element/ElementCollection',
    'Orders/SaveCheckboxes',
    'cg-mustache',
    'popup/confirm',
    'filters'
], function(
    OrdersBulkActionAbstract,
    elementCollection,
    saveCheckboxes,
    CGMustache,
    Confirm,
    Filters
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
        var self = this;
        var datatable = this.getDataTableElement();
        var orders = this.getOrders();
        var dataToSubmit = this.getDataToSubmit();

        if (!datatable.length || !orders.length) {
            return;
        }
        if (this.getElement().data('action') == 'remove') {
            return this.remove();
        }

        $.ajax({
            url: '/orders/batch/checkAssociation',
            type: 'POST',
            dataType: 'json',
            data: dataToSubmit,
            context: this,
            success : function(data)
            {
                var ordersAlreadyInBatches = [];
                $.each(data.batchMap, function(index, value){
                    if (value.batch > 0) {
                        ordersAlreadyInBatches.push({
                            'key': value.orderId,
                            'value': value.batch
                        });
                    }
                });

                if (ordersAlreadyInBatches.length) {
                    this.showPopup(ordersAlreadyInBatches);
                } else {
                    this.createBatch(orders, dataToSubmit);
                }
            },
            error: function (error, textStatus, errorThrown)
            {
                return self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    Batch.prototype.showPopup = function(ordersAlreadyInBatches)
    {
        var self = this;
        var templateMap = {
            'messageTemplate' : '/channelgrabber/orders/template/Batch/batchAlreadyExistsMessage.mustache',
            'tableTemplate' : '/channelgrabber/zf2-v4-ui/templates/popups/hashTable.mustache'
        };
        CGMustache.get().fetchTemplates(templateMap, function(templates, cgmustache)
        {
            var tableTemplateParams = {
                'keyHeader': 'Order ID',
                'valueHeader': 'Batch',
                'rows': ordersAlreadyInBatches
            };

            var table = cgmustache.renderTemplate(templates, tableTemplateParams, 'tableTemplate');
            var messageTemplateParams = {
                'ordersTable': table
            };
            var message = cgmustache.renderTemplate(templates, messageTemplateParams, 'messageTemplate');
            var confirm = new Confirm(message, function(answer){
                self.confirmAction(ordersAlreadyInBatches, answer);
            });
        });
    };

    Batch.prototype.confirmAction = function(ordersAlreadyInBatches, answer)
    {
        if (!answer) {
            return;
        }
        var orders = this.getOrders();
        var dataToSubmit = this.getDataToSubmit();

        if (answer === 'No') {
            $.each(ordersAlreadyInBatches, function(key, element){
                var index = dataToSubmit.orders.indexOf(element.key);

                if (index > -1) {
                    dataToSubmit.orders.splice(index, 1);
                }
            });
        }

        if (! dataToSubmit.orders.length) {
            return;
        }

        this.createBatch(orders, dataToSubmit);
    };

    Batch.prototype.createBatch = function(orders, dataToSubmit)
    {
        var self = this;
        var ajax = {
            url: this.getElement().data('url'),
            type: 'POST',
            dataType: 'json',
            data: dataToSubmit,
            context: this,
            success : this.actionSuccess,
            error: function (error, textStatus, errorThrown) {
                return self.getNotificationHandler().ajaxError(error, textStatus, errorThrown);
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

        $.each(data['batches'].reverse().slice(0, Filters().getMaxItemsToDisplayInSidebar()), function(index)
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
