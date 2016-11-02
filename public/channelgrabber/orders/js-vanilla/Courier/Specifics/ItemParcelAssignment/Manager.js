define([
    '../ItemParcelAssignment.js',
    'cg-mustache',
    'popup/generic',
], function(
    ItemParcelAssignment,
    CGMustache,
    Popup
) {
    function Manager(templateMap)
    {
        var instances = [];
        var popup;

        this.getTemplateMap = function()
        {
            return templateMap;
        };

        this.getInstances = function()
        {
            return instances;
        };

        this.addInstance = function(instance)
        {
            instances.push(instance);
            return this;
        };

        this.deleteInstances = function()
        {
            for (var index in instances) {
                delete instances[index];
            }
            instances = [];
            return this;
        };

        this.getPopup = function()
        {
            return popup;
        };

        this.setPopup = function(newPopup)
        {
            popup = newPopup;
            return this;
        };
    }

    Manager.SELECTOR_INPUT = 'input.courier-order-itemParcelAssignment';
    Manager.POPUP_WIDTH_PX = 400;
    Manager.POPUP_HEIGHT_PX = 'auto';

    Manager.prototype.createInstances = function(dataTable, service)
    {
        this.deleteInstances();
        if ($(Manager.SELECTOR_INPUT, dataTable).length == 0) {
            return;
        }
        var self = this;
        var popup = this.getPopup() || this.createPopup();
        CGMustache.get().fetchTemplates(this.getTemplateMap(), function(templates, cgMustache)
        {
            $(Manager.SELECTOR_INPUT, dataTable).each(function()
            {
                var element = this;
                var orderId = element.dataset.orderId;
                var parcelNumber = element.dataset.parcelNumber;
                var orderData = service.getDataForOrder(orderId);
                var instance = new ItemParcelAssignment(element, orderId, orderData, parcelNumber, templates, popup);
                self.addInstance(instance);
            });
        });
    };

    Manager.prototype.createPopup = function()
    {
        var popup = new Popup('', Manager.POPUP_WIDTH_PX, Manager.POPUP_HEIGHT_PX);
        this.setPopup(popup);
        return popup;
    };

    Manager.prototype.clearForOrder = function(orderId)
    {
        var instances = this.getInstances();
        for (var index in instances) {
            if (instances[index].getOrderId() != orderId) {
                continue;
            }
            instances[index].clear();
        }
    };

    return Manager;
});