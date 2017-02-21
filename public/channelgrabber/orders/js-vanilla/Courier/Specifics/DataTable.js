function CourierSpecificsDataTable(dataTable, orderIds, courierAccountId, orderServices, templateMap, courier)
{
    CourierDataTableAbstract.call(this, dataTable, orderIds, templateMap);

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
            self.setupResizeFunctions()
                .addOrderIdsToAjaxRequest()
                .addOrderServicesToAjaxRequest()
                .addElementsToColumns()
                .disableInputsForCreatedLabels()
                .disableInputsForNonRequiredOptions();
        });
        dataTable.on('fnServerData', function()
        {
            self.distinctStatusActions = {};
        });
        dataTable.on('fnDrawCallback', function()
        {
            self.setBulkActionButtons()
                .hideColumnsForCourier(courier)
                .triggerInitialItemWeightKeypress();
        });
    };
    init.call(this);
}

CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';
CourierSpecificsDataTable.SELECTOR_PARCELS_ELEMENT = '#courier-parcels-input-container';
CourierSpecificsDataTable.SELECTOR_DATEPICKER_ELEMENT = '#courier-order-collectionDate-container';
CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS = '#courier-action-buttons .button-holder';
CourierSpecificsDataTable.SELECTOR_ITEM_WEIGHT_INPUT = '.courier-item-weight';
CourierSpecificsDataTable.SELECTOR_ITEM_PARCELS_ASSIGN = '#courier-itemParcelAssignment-button-container .button-holder';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_CONTAINER = '#courier-specifics-bulk-actions';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS = '#courier-specifics-bulk-actions div.courier-status-all-labels-button';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_SUFFIX = '-all-labels-button-shadow';

CourierSpecificsDataTable.labelStatusActions = {
    '': {'create': true},
    'not printed': {'print': true, 'cancel': true},
    'printed': {'print': true},
    'cancelled': {'create': true},
    'creating': {},
    'dispatched': {'cancel': true}
};

CourierSpecificsDataTable.columnRenderers = {
    service: "addCustomSelectToServiceColumn",
    parcels: "addInlineTextToParcelsColumn",
    collectionDate: "addDatePickerToCollectionDateColumn",
    actions: "addButtonsToActionsColumn",
    itemParcelAssignment: "addItemParcelAssignmentButtonColumn",
    packageType: "addCustomSelectToPackageTypeColumn",
    addOns: "addCustomSelectToAddOnsColumn"
};

CourierSpecificsDataTable.hiddenColumnsForCourier = {
    'royal-mail-nd': ['itemParcelAssignment']
};

CourierSpecificsDataTable.prototype = Object.create(CourierDataTableAbstract.prototype);

/**
 * @protected
 */
CourierSpecificsDataTable.prototype.distinctStatusActions = {};

CourierSpecificsDataTable.prototype.setupResizeFunctions = function()
{
    function heightResizeFunction()
    {
        var headingHeight = $('.heading-large').outerHeight();
        var fixedHeaderHeight = $('#fixed-header').outerHeight();

        $('.dataTables_wrapper').height('calc(100% - ' + headingHeight + 'px)');
        $('.dataTables_scroll').height('calc(100% - ' + fixedHeaderHeight + 'px)');
        $('.dataTables_scrollBody').height('calc(100% - ' + fixedHeaderHeight + 'px)');
    }

    this.getDataTable().cgDataTable('addResizeOverrideFunctions', heightResizeFunction);
    return this;
};

CourierSpecificsDataTable.prototype.addOrderServicesToAjaxRequest = function()
{
    var orderServices = this.getOrderServices();
    // We just need this for the initial load, Service.js::refresh() will take care of it after that
    this.getDataTable().one("fnServerData", function(event, sSource, aoData, fnCallback, oSettings)
    {
        for (var orderId in orderServices)
        {
            aoData.push({
                'name': 'orderData['+orderId+'][service]',
                'value': orderServices[orderId]
            });
        }
    });
    return this;
};

CourierSpecificsDataTable.prototype.addElementsToColumns = function()
{
    var self = this;
    this.getDataTable().on('renderColumn', function(event, cgmustache, template, column, data)
    {
        if (!CourierSpecificsDataTable.columnRenderers.hasOwnProperty(column.mData)) {
            return;
        }
        var method = CourierSpecificsDataTable.columnRenderers[column.mData];
        self[method](data, cgmustache);
    });
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
    templateData.parcelsInput = CourierSpecificsDataTable.elementToHtmlString(elementCopy);
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
    templateData.collectionDatePicker = CourierSpecificsDataTable.elementToHtmlString(elementCopy);
    return this;
};

CourierSpecificsDataTable.prototype.addButtonsToActionsColumn = function(templateData)
{
    if (!templateData.actionRow) {
        return;
    }
    var actions = this.getActionsFromRowData(templateData);
    if (!actions || $.isEmptyObject(actions)) {
        return this.addStatusLozengeToActionsColumn(templateData);
    }
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
        buttonsHtml += CourierSpecificsDataTable.elementToHtmlString(buttonCopy);
    });
    templateData.actions = buttonsHtml;
    return this;
};

CourierSpecificsDataTable.prototype.addStatusLozengeToActionsColumn = function(rowData)
{
    var statusDisplay = String(rowData.labelStatus).ucfirst();
    rowData.actions = '<span class="status ' + rowData.labelStatus + '">' + statusDisplay + '</span>';
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

CourierSpecificsDataTable.prototype.addItemParcelAssignmentButtonColumn = function(templateData)
{
    if (!templateData.parcelRow || templateData.itemParcelAssignmentButton) {
        return;
    }
    var elementCopy = $(CourierSpecificsDataTable.SELECTOR_ITEM_PARCELS_ASSIGN).clone();
    var id = $('input.button', elementCopy).attr('id') + '_' + templateData.orderId + '_' + templateData.parcelNumber;
    $('input.button', elementCopy).attr('id', id);
    $('div.button', elementCopy).attr('id', id+'-shadow');
    templateData.itemParcelAssignmentButton = CourierSpecificsDataTable.elementToHtmlString(elementCopy);
    return this;
};

CourierSpecificsDataTable.prototype.addCustomSelectToPackageTypeColumn = function(templateData, cgMustache)
{
    this.fetchTemplate('select', cgMustache, function(template)
    {
        var data = {
            id: 'courier-package-type_' + templateData.orderId,
            name: 'orderData[' + templateData.orderId + '][packageType]',
            class: 'required',
            options: []
        };
        for (var index in templateData.packageTypes) {
            data.options.push({
                title: templateData.packageTypes[index],
                selected: (templateData.packageTypes[index] == templateData.packageType)
            });
        }
        templateData.packageTypeOptions = cgMustache.renderTemplate(template, data);
    }, true);
};

CourierSpecificsDataTable.prototype.addCustomSelectToAddOnsColumn = function(templateData, cgMustache)
{
    this.fetchTemplate('multiselect', cgMustache, function(template)
    {
        var data = {
            id: 'courier-add-ons_' + templateData.orderId,
            name: 'orderData[' + templateData.orderId + '][addOn]',
            emptyTitle: "No add-ons",
            searchField: false,
            options: []
        };
        for (var index in templateData.addOns) {
            templateData.addOns[index].selected = (templateData.addOn && templateData.addOn.indexOf(templateData.addOns[index].title) > -1);
            data.options.push(templateData.addOns[index]);
        }
        templateData.addOnsOptions = cgMustache.renderTemplate(template, data);
    }, true);
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
    return this;
};

CourierSpecificsDataTable.prototype.hideColumnsForCourier = function(courier)
{
    if (! CourierSpecificsDataTable.hiddenColumnsForCourier[courier]) {
        return this;
    }

    var hiddenColumns = 0;
    $('#datatable').dataTable().fnSettings().aoColumns.forEach(function (column) {
         if(CourierSpecificsDataTable.hiddenColumnsForCourier[courier].indexOf(column.mData) > -1) {
             var columnId = column.aDataSort[0];
             $('#datatable').dataTable().fnSetColumnVis(columnId, false, false);
             hiddenColumns++;
         }
    });
    $('.courier-group-row td').each(function(){
        var newColspan = parseInt($(this).attr('colspan')) - hiddenColumns;
        $(this).attr('colspan', newColspan);
    });
    $('#datatable').cgDataTable('adjustTable');

    return this;
};

CourierSpecificsDataTable.prototype.disableInputsForNonRequiredOptions = function()
{
    this.getDataTable().on('fnRowCallback', function(event, nRow, aData)
    {
        if (aData.labelStatus != '' && aData.labelStatus != 'cancelled') {
            return;
        }

        var orderId = aData.orderId;
        var parcelNumber = (typeof aData.parcelNumber != 'undefined' ? aData.parcelNumber : 0);
        var itemId = (typeof aData.itemId != 'undefined' ? aData.itemId : 0);
        for (var name in aData.requiredFields) {
            var selector = 'input[name="orderData['+orderId+']['+name+']"]'
                + ', input[name="parcelData['+orderId+']['+parcelNumber+']['+name+']"]'
                + ', input[name="itemData['+orderId+']['+itemId+']['+name+']"]';
            var elements = $(nRow).find(selector);
            if (aData.requiredFields[name].show) {
                elements.removeAttr('disabled').removeClass('disabled');
                if (elements.parent().hasClass('custom-select')) {
                    elements.parent().removeClass('disabled');
                }
                if (aData.requiredFields[name].required) {
                    elements.addClass('required');
                } else {
                    elements.removeClass('required');
                }
                elements.each(function()
                {
                    if ($(this).data('placeholder')) {
                        $(this).attr('placeholder', $(this).data('placeholder'));
                    }
                });
            } else {
                elements.attr('disabled', 'disabled').removeClass('required').addClass('disabled');
                if (elements.parent().hasClass('custom-select')) {
                    elements.parent().addClass('disabled');
                }
                elements.each(function()
                {
                    $(this).data('placeholder', $(this).attr('placeholder'));
                    $(this).attr('placeholder', 'N/A');
                });
            }
        }
    });
    return this;
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
        var id = $('input.button', buttonTemplate).attr('id');
        var action = id.split('-')[0];
        if (!actions[action]) {
            return true; //continue
        }
        var buttonCopy = $(buttonTemplate).clone();
        id += '_' + orderId;
        $('input.button', buttonCopy).attr('id', id);
        $('div.button', buttonCopy).attr('id', id+'-shadow');
        buttonsHtml += CourierSpecificsDataTable.elementToHtmlString(buttonCopy);
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

CourierSpecificsDataTable.elementToHtmlString = function(element)
{
    // Easiest way: add to a temporary element then get its innerHTML
    return $('<div>').append(element).html();
};
