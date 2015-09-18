function CourierSpecificsDataTable(dataTable, orderIds, courierId, orderServices)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds);

    this.getCourierId = function()
    {
        return courierId;
    };

    this.getOrderServices = function()
    {
        return orderServices;
    };

    this.unsetOrderService = function(orderId) {
        delete orderServices[orderId];
    };

    var init = function()
    {
        var self = this;
        dataTable.on('before-cgdatatable-init', function()
        {
            self.addOrderIdsToAjaxRequest()
                .addElementsToColumns();
        });
    };
    init.call(this);
}

CourierSpecificsDataTable.COLUMN_SERVICE = 'service';
CourierSpecificsDataTable.COLUMN_PARCELS = 'parcels';
CourierSpecificsDataTable.COLUMN_ACTIONS = 'actions';
CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';
CourierSpecificsDataTable.SELECTOR_PARCELS_ELEMENT = '#courier-parcels-input-container';
CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS = '#courier-action-buttons .button-holder';

CourierSpecificsDataTable.prototype = Object.create(CourierDataTableAbstract.prototype);

CourierSpecificsDataTable.prototype.addElementsToColumns = function()
{
    var self = this;
    this.getDataTable().on('renderColumn', function(event, cgmustache, template, column, data)
    {
        if (column.mData == CourierSpecificsDataTable.COLUMN_SERVICE) {
            return self.addCustomSelectToServiceColumn(data);
        }
        if (column.mData == CourierSpecificsDataTable.COLUMN_PARCELS) {
            return self.addInlineTextToParcelsColumn(data);
        }
        if (column.mData == CourierSpecificsDataTable.COLUMN_ACTIONS) {
            return self.addButtonsToActionsColumn(data);
        }
    });
    return this;
};

CourierSpecificsDataTable.prototype.addCustomSelectToServiceColumn = function(templateData)
{
    var name = 'orderData['+templateData.orderId+'][service]';
    var templateSelector = CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX+this.getCourierId();
    // Unset the local service once we've got it so we don't override it after future changes
    var service = this.getAndUnsetOrderService(templateData.orderId);
    if (!service) {
        service = templateData.service;
    }
    var serviceSelectCopy = this.cloneCustomSelectElement(
        templateSelector, name, 'courier-service-custom-select', service
    );
    templateData.serviceOptions = $('<div>').append(serviceSelectCopy).html();
    return this;
};

CourierSpecificsDataTable.prototype.addInlineTextToParcelsColumn = function(templateData)
{
    if (!templateData.orderRow || templateData.parcelsInput) {
        return;
    }
    var elementCopy = $(CourierSpecificsDataTable.SELECTOR_PARCELS_ELEMENT).clone();
    var name = 'orderData[' + templateData.orderId+'][parcels]';
    var id = $('input', elementCopy).attr('id') + '-' + templateData.orderId;
    $(elementCopy).attr('id', id+'-container');
    $('input', elementCopy)
        .attr('id', id)
        .attr('name', name)
        .attr('data-element-name', name)
        // .val() doesn't work here, possibly because its not part of the DOM yet
        .attr('value', templateData.parcels);
    templateData.parcelsInput = $('<div>').append(elementCopy).html();
};

CourierSpecificsDataTable.prototype.addButtonsToActionsColumn = function(templateData)
{
    if (!templateData.actionRow) {
        return;
    }
    var buttonsHtml = '';
    $(CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS).each(function()
    {
        var buttonTemplate = this;
        var buttonCopy = $(buttonTemplate).clone();
        var id = $('input.button', buttonCopy).attr('id') + '-' + templateData.orderId;
        $('input.button', buttonCopy).attr('id', id);
        $('div.button', buttonCopy).attr('id', id+'-shadow');
        buttonsHtml += $('<div>').append(buttonCopy).html();
    });
    templateData.actions = buttonsHtml;
};

CourierSpecificsDataTable.prototype.getAndUnsetOrderService = function(orderId)
{
    var service = this.getOrderServices()[orderId];
    this.unsetOrderService(orderId);
    return service;
};