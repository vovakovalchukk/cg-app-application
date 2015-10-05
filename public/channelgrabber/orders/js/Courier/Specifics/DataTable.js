function CourierSpecificsDataTable(dataTable, orderIds, courierAccountId, orderServices)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds);

    this.getCourierAccountId = function()
    {
        return courierAccountId;
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
                .addElementsToColumns()
                .disableInputsForCreatedLabels();
        });
        dataTable.on('fnPreDrawCallback', function()
        {
            self.distinctStatusActions = {};
        });
        dataTable.on('fnDrawCallback', function()
        {
            self.setBulkActionButtons()
                .triggerInitialItemWeightKeypress();
        });
    };
    init.call(this);
}

CourierSpecificsDataTable.COLUMN_SERVICE = 'service';
CourierSpecificsDataTable.COLUMN_PARCELS = 'parcels';
CourierSpecificsDataTable.COLUMN_COLLECTION_DATE = 'collectionDate';
CourierSpecificsDataTable.COLUMN_ACTIONS = 'actions';
CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';
CourierSpecificsDataTable.SELECTOR_PARCELS_ELEMENT = '#courier-parcels-input-container';
CourierSpecificsDataTable.SELECTOR_DATEPICKER_ELEMENT = '#courier-order-collectionDate-container';
CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS = '#courier-action-buttons .button-holder';
CourierSpecificsDataTable.SELECTOR_ITEM_WEIGHT_INPUT = '.courier-item-weight';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_CONTAINER = '#courier-specifics-bulk-actions';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS = '#courier-specifics-bulk-actions div.button';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_SUFFIX = '-all-labels-button-shadow';

CourierSpecificsDataTable.labelStatusActions = {
    '': {'create': true},
    'not printed': {'print': true, 'cancel': true},
    'printed': {'print': true},
    'cancelled': {'create': true},
    'creating': {}
};

CourierSpecificsDataTable.prototype = Object.create(CourierDataTableAbstract.prototype);

/**
 * @protected
 */
CourierSpecificsDataTable.prototype.distinctStatusActions = {};

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
        if (column.mData == CourierSpecificsDataTable.COLUMN_COLLECTION_DATE) {
            return self.addDatePickerToCollectionDateColumn(data);
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
    var templateSelector = CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX+this.getCourierAccountId();
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
    var id = $('input', elementCopy).attr('id') + '_' + templateData.orderId;
    $(elementCopy).attr('id', id+'-container');
    $('input', elementCopy)
        .attr('id', id)
        .attr('name', name)
        .attr('data-element-name', name)
        // .val() doesn't work here, possibly because its not part of the DOM yet
        .attr('value', templateData.parcels);
    templateData.parcelsInput = $('<div>').append(elementCopy).html();
    return this;
};

CourierSpecificsDataTable.prototype.addDatePickerToCollectionDateColumn = function(templateData)
{
    if (!templateData.orderRow || templateData.collectionDatePicker) {
        return;
    }
    var elementCopy = $(CourierSpecificsDataTable.SELECTOR_DATEPICKER_ELEMENT).clone();
    var name = 'orderData[' + templateData.orderId+'][collectionDate]';
    var id = $('input[type="hidden"]', elementCopy).attr('id') + '_' + templateData.orderId;
    $(elementCopy).attr('id', id+'-container');
    $('input[type="hidden"]', elementCopy)
        .attr('id', id)
        .attr('name', name)
        .attr('value', templateData.collectionDate);
    $('input[type!="hidden"]', elementCopy)
        .attr('id', id+'-datepicker')
        .attr('data-element-name', name)
        .attr('value', templateData.collectionDate.replace(/(\d{4})-(\d{2})-(\d{2})/, '$3/$2/$1'))
        .removeClass('hasDatepicker');
    templateData.collectionDatePicker = $('<div>').append(elementCopy).html();
    return this;
};

CourierSpecificsDataTable.prototype.addButtonsToActionsColumn = function(templateData)
{
    if (!templateData.actionRow) {
        return;
    }
    if (templateData.labelStatus == 'creating') {
        return this.addCreatingMessageToActionsColumn(templateData);
    }
    var actions = this.getActionsFromRowData(templateData);
    this.trackDistinctStatusActions(actions);
    var buttonsHtml = '';
    $(CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS).each(function()
    {
        var buttonTemplate = this;
        var id = $('input.button', buttonTemplate).attr('id')
        var action = id.split('-')[0];
        if (!actions[action]) {
            return true; //continue
        }
        var buttonCopy = $(buttonTemplate).clone();
        id += '_' + templateData.orderId;
        $('input.button', buttonCopy).attr('id', id);
        $('div.button', buttonCopy).attr('id', id+'-shadow');
        buttonsHtml += $('<div>').append(buttonCopy).html();
    });
    templateData.actions = buttonsHtml;
    return this;
};

CourierSpecificsDataTable.prototype.addCreatingMessageToActionsColumn = function(rowData)
{
    rowData.actions = '<span class="status processing">Creating</span>';
    return this;
};

CourierSpecificsDataTable.prototype.getActionsFromRowData = function(rowData)
{
    return CourierSpecificsDataTable.getActionsFromLabelStatus(rowData.labelStatus, rowData.cancellable);
};

CourierSpecificsDataTable.prototype.trackDistinctStatusActions = function(actions)
{
    for (var action in actions) {
        if (this.distinctStatusActions[action]) {
            continue;
        }
        this.distinctStatusActions[action] = true;
    }
    return this;
};

CourierSpecificsDataTable.prototype.disableInputsForCreatedLabels = function()
{
    this.getDataTable().on('fnRowCallback', function(event, nRow, aData)
    {
        if (aData.labelStatus == '' || aData.labelStatus == 'cancelled') {
            return;
        }
        $('input, .custom-select', nRow).attr('disabled', 'disabled').addClass('disabled');
    });
};

CourierSpecificsDataTable.prototype.setBulkActionButtons = function()
{
    $(CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS).hide();
    var actions = this.distinctStatusActions;
    // If there's items still left to be created then only show 'Create all'
    if (actions.create) {
        actions = {"create": true};
    }
    for (var action in actions) {
        $('#' + action + CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_SUFFIX).show();
    }
    $(CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_CONTAINER).show();
    return this;
};

CourierSpecificsDataTable.prototype.triggerInitialItemWeightKeypress = function()
{
    $(CourierSpecificsDataTable.SELECTOR_ITEM_WEIGHT_INPUT).each(function()
    {
        $(this).trigger('keyup');
    });
    return this;
};

CourierSpecificsDataTable.prototype.getAndUnsetOrderService = function(orderId)
{
    var service = this.getOrderServices()[orderId];
    this.unsetOrderService(orderId);
    return service;
};

// The following methods are static so they can be accessed here and in the Service
CourierSpecificsDataTable.getButtonsHtmlForActions = function(actions, orderId)
{
    var buttonsHtml = '';
    $(CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS).each(function()
    {
        var buttonTemplate = this;
        var id = $('input.button', buttonTemplate).attr('id')
        var action = id.split('-')[0];
        if (!actions[action]) {
            return true; //continue
        }
        var buttonCopy = $(buttonTemplate).clone();
        id += '_' + orderId;
        $('input.button', buttonCopy).attr('id', id);
        $('div.button', buttonCopy).attr('id', id+'-shadow');
        buttonsHtml += $('<div>').append(buttonCopy).html();
    });
    return buttonsHtml;
};

CourierSpecificsDataTable.getActionsFromLabelStatus = function(labelStatus, cancellable)
{
    var actions = this.labelStatusActions[labelStatus];
    if (actions['cancel'] && !cancellable) {
        delete actions['cancel'];
    }
    return actions;
};