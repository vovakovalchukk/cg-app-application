define([
    'cg-mustache'
], function(
    CGMustache
) {
    function ItemParcelAssignment(element, orderId, orderData, parcelNumber, templates, popup)
    {
        this.getElement = function()
        {
            return element;
        };

        this.getOrderId = function()
        {
            return orderId;;
        };

        this.getOrderData = function()
        {
            return orderData;
        };

        this.getParcelNumber = function()
        {
            return parcelNumber;
        };

        this.getTemplates = function()
        {
            return templates;
        };

        this.getPopup = function()
        {
            return popup;
        };

        var init = function()
        {
            this.listenForButtonClick();
            if (orderData.parcels.length == 1) {
                this.setDataForSingleParcel()
                    .disableAssignButton();
            }
            if ($(element).val()) {
                this.markAsAssigned();
            }
            if (orderData.labelStatus != '') {
                this.disableAssignButton();
            }
        };
        init.call(this);
    }

    ItemParcelAssignment.SELECTOR_PARENT = 'td';
    ItemParcelAssignment.SELECTOR_BUTTON = 'div.button';
    ItemParcelAssignment.SELECTOR_POPUP = '.courier-item-parcel-assignment-popup';
    ItemParcelAssignment.SELECTOR_ITEM_WEIGHT = 'input[name="itemData[{orderId}][{itemId}][weight]"]';
    ItemParcelAssignment.SELECTOR_PARCEL_WEIGHT = 'input[name="parcelData[{orderId}][{parcelNumber}][weight]"]';
    ItemParcelAssignment.ITEM_QTY_ID = 'courier-itemParcelAssignment-qty_{orderId}_{parcelNumber}_{itemId}';

    ItemParcelAssignment.prototype.listenForButtonClick = function()
    {
        var self = this;
        var button = this.getAssignButton();
        button.click(function()
        {
            if ($(this).hasClass('disabled')) {
                return;
            }
            self.showPopup();
        });
    };

    ItemParcelAssignment.prototype.setDataForSingleParcel = function()
    {
        var assignmentData = {};
        var items = this.getOrderData().items;
        for (var index in items) {
            var item = items[index];
            assignmentData[item.id] = item.quantity;
        }
        this.storeAssignmentData(assignmentData);
        return this;
    };

    ItemParcelAssignment.prototype.storeAssignmentData = function(assignmentData)
    {
        $(this.getElement()).val(JSON.stringify(assignmentData));
        return this;
    };

    ItemParcelAssignment.prototype.markAsAssigned = function()
    {
        var button = this.getAssignButton();
        button.find('.title').html('Assigned &#10003;');
        return this;
    };

    ItemParcelAssignment.prototype.disableAssignButton = function()
    {
        this.getAssignButton().addClass('disabled');
    };

    ItemParcelAssignment.prototype.showPopup = function()
    {
        var content = this.renderPopupContent();
        this.getPopup().htmlContent(content);
        this.listenForPopupButtonClick();
        this.getPopup().show();
    };

    ItemParcelAssignment.prototype.renderPopupContent = function()
    {
        var cgMustache = CGMustache.get();
        var buttons = cgMustache.renderTemplate(this.getTemplates(), {"buttons": [{"value": "Assign"}]}, 'buttons');
        var partials = {"actionButtons": buttons};
        return cgMustache.renderTemplate(this.getTemplates(), {
            "parcelNumber": this.getParcelNumber(),
            "items": this.getItemOptionsForPopupContent()
        }, 'itemParcelAssignment', partials);
    };

    ItemParcelAssignment.prototype.getItemOptionsForPopupContent = function()
    {
        var assignmentData = {};
        if ($(this.getElement()).val()) {
            assignmentData = JSON.parse($(this.getElement()).val());
        }
        var orderData = this.getOrderData();
        var itemOptions = [];
        for (var index in orderData.items) {
            var item = orderData.items[index];
            var value = assignmentData[item.id] || 0;
            var itemQtyInput = CGMustache.get().renderTemplate(this.getTemplates(), {
                "name": "itemQty_"+item.id,
                "type": "number",
                "min": "0",
                // Not sure why but if you pass this through as an integer it breaks the Mustache template
                "max": String(item.quantity),
                "value": value
            }, 'inlineText');
            itemOptions.push({
                "name": item.name,
                "qtyInput": itemQtyInput
            });
        }
        return itemOptions;
    };

    ItemParcelAssignment.prototype.listenForPopupButtonClick = function()
    {
        var self = this;
        $(ItemParcelAssignment.SELECTOR_POPUP + ' ' + ItemParcelAssignment.SELECTOR_BUTTON).one('click', function()
        {
            self.processAssignmentSelection();
            self.getPopup().hide();
        });
    };

    ItemParcelAssignment.prototype.processAssignmentSelection = function()
    {
        var self = this;
        var assignmentData = {};
        var parcelWeight = 0;
        $(ItemParcelAssignment.SELECTOR_POPUP + ' table input').each(function()
        {
            var input = this;
            var value = parseInt($(input).val());
            if (value == NaN || value <= 0) {
                return true; // continue
            }
            var itemId = $(input).attr('name').split('_').pop();
            assignmentData[itemId] = value;
            parcelWeight += (self.getUnitWeightForItem(itemId) * value) ;
        });

        this.storeAssignmentData(assignmentData)
            .markAsAssigned()
            .setParcelWeight(parcelWeight);
    };

    ItemParcelAssignment.prototype.getUnitWeightForItem = function(itemId)
    {
        var weightSelector = ItemParcelAssignment.SELECTOR_ITEM_WEIGHT
            .replace('{orderId}', this.getOrderId())
            .replace('{itemId}', itemId);
        var totalWeight = $(weightSelector).val();
        if (!totalWeight) {
            return 0;
        }
        var items = this.getOrderData().items;
        var item = null;
        for (var index in items) {
            if (items[index].id == itemId) {
                item = items[index];
                break;
            }
        }
        if (!item) {
            return 0;
        }
        return totalWeight / item.quantity;
    };

    ItemParcelAssignment.prototype.clear = function()
    {
        $(this.getElement()).val('');
        return this;
    };

    ItemParcelAssignment.prototype.getAssignButton = function()
    {
        return $(this.getElement()).closest(ItemParcelAssignment.SELECTOR_PARENT).find(ItemParcelAssignment.SELECTOR_BUTTON);
    };

    ItemParcelAssignment.prototype.setParcelWeight = function(parcelWeight)
    {
        var weightSelector = ItemParcelAssignment.SELECTOR_PARCEL_WEIGHT
            .replace('{orderId}', this.getOrderId())
            .replace('{parcelNumber}', this.getParcelNumber());
        $(weightSelector).val(parcelWeight);
        return this;
    };

    return ItemParcelAssignment;
});