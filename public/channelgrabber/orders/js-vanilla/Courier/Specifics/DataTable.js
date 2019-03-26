function CourierSpecificsDataTable(dataTable, orderIds, courierAccountId, orderServices, templateMap)
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
                .disableInputsForNonRequiredOptions()
                .listenForDimensionsChange();
        });
        dataTable.on('fnServerData fnPreRowsUpdatedCallback', function()
        {
            self.distinctStatusActions = {};
        });
        dataTable.on('fnDrawCallback fnRowsUpdatedCallback', function()
        {
            self.setBulkActionButtons()
                .triggerInitialItemWeightKeypress();
        });
    };
    init.call(this);
}

CourierSpecificsDataTable.SELECTOR_ACCOUNT_BALANCE_FIGURE = '.shipping-ledger-balance-amount';
CourierSpecificsDataTable.SELECTOR_SERVICE_SELECT_PREFIX = '#courier-service-select-';
CourierSpecificsDataTable.SELECTOR_PARCELS_ELEMENT = '#courier-parcels-input-container';
CourierSpecificsDataTable.SELECTOR_DATEPICKER_ELEMENT = '#courier-order-collectionDate-container';
CourierSpecificsDataTable.SELECTOR_ACTION_BUTTONS = '#courier-action-buttons .button-holder';
CourierSpecificsDataTable.SELECTOR_ITEM_WEIGHT_INPUT = '.courier-item-weight';
CourierSpecificsDataTable.SELECTOR_ITEM_PARCELS_ASSIGN = '#courier-itemParcelAssignment-button-container .button-holder';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_CONTAINER = '#courier-specifics-bulk-actions';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS = '#courier-specifics-bulk-actions div.courier-status-all-labels-button';
CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_SUFFIX = '-all-labels-button-shadow';
CourierSpecificsDataTable.SELECTOR_COURIER_ORDER_DIMENSIONS = '.courier-order-dimension';
CourierSpecificsDataTable.LABEL_STATUS_DEFAULT = 'not printed';
CourierSpecificsDataTable.LABEL_STATUS_CANCELLED = 'cancelled';
CourierSpecificsDataTable.LABEL_STATUS_RATES_FETCHED = 'rates fetched';
CourierSpecificsDataTable.SELECTOR_ACTIONS_PREFIX = '#courier-actions-';
CourierSpecificsDataTable.SELECTOR_NAV_FORM = '#courier-specifics-nav-form';
CourierSpecificsDataTable.SELECTOR_LABEL_FORM = '#courier-specifics-label-form';
CourierSpecificsDataTable.SELECTOR_ORDER_ID_INPUT = '#datatable input[name="order[]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_LABEL_STATUS_TPL = '#datatable input[name="orderInfo[_orderId_][labelStatus]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_EXPORTABLE_TPL = '#datatable input[name="orderInfo[_orderId_][exportable]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_CANCELLABLE_TPL = '#datatable input[name="orderInfo[_orderId_][cancellable]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_DISPATCHABLE_TPL = '#datatable input[name="orderInfo[_orderId_][dispatchable]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_RATEABLE_TPL = '#datatable input[name="orderInfo[_orderId_][rateable]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_CREATABLE_TPL = '#datatable input[name="orderInfo[_orderId_][creatable]"]';
CourierSpecificsDataTable.SELECTOR_ORDER_ROW_TPL = '#courier-order-row_{orderId}';
CourierSpecificsDataTable.SELECTOR_ACTIONS_PREFIX = '#courier-actions-';
CourierSpecificsDataTable.SELECTOR_SERVICE_PREFIX = '#courier-service-options-';
CourierSpecificsDataTable.SELECTOR_FETCH_ALL_RATES_BUTTON = '#fetchrates-all-labels-button-shadow';
CourierSpecificsDataTable.SELECTOR_CREATE_ALL_LABELS_BUTTON = '#create-all-labels-button-shadow';
CourierSpecificsDataTable.SELECTOR_TOTAL_ORDER_LABEL_COST = '.order-total-label-cost';
CourierSpecificsDataTable.SELECTOR_CURRENCY_SYMBOL_DISPLAY = '.total-cost .currency';
CourierSpecificsDataTable.SELECTOR_COST_COLUMN_INPUT = '.courier-label-cost';

CourierSpecificsDataTable.labelStatusActions = {
    '': {'create': true, 'export': true, 'fetchrates': true},
    'exported': {"export": true},
    'not printed': {'print': true, 'cancel': true, 'dispatch': true},
    'printed': {'print': true, 'dispatch': true},
    'cancelled': {'create': true, 'fetchrates': true},
    'creating': {},
    'dispatched': {'cancel': true},
    'rates fetched': {'create': true, 'fetchrates': true}
};

CourierSpecificsDataTable.columnRenderers = {
    service: "addCustomSelectToServiceColumn",
    parcels: "addInlineTextToParcelsColumn",
    collectionDate: "addDatePickerToCollectionDateColumn",
    actions: "addButtonsToActionsColumn",
    itemParcelAssignment: "addItemParcelAssignmentButtonColumn",
    packageType: "addCustomSelectToPackageTypeColumn",
    addOns: "addCustomSelectToAddOnsColumn",
    deliveryExperience: "addCustomSelectToDeliveryExperienceColumn",
    insuranceOptions: "addCustomSelectToInsuranceOptionsColumn"
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
    return CourierSpecificsDataTable.getActionsFromLabelStatus(
        rowData.labelStatus,
        rowData.exportable,
        rowData.cancellable,
        rowData.dispatchable,
        rowData.rateable,
        rowData.creatable
    );
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
    var optionsObject = this.convertDataToSelectTemplateFormat(templateData.packageTypes);
    this.fetchTemplate('select', cgMustache, function(template)
    {
        var data = {
            id: 'courier-package-type_' + templateData.orderId,
            name: 'orderData[' + templateData.orderId + '][packageType]',
            class: 'required',
            options: []
        };
        for (var value in optionsObject.options) {
            data.options.push({
                title: optionsObject.options[value].title,
                value: value,
                selected: (value == optionsObject.selected)
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

CourierSpecificsDataTable.prototype.addCustomSelectToDeliveryExperienceColumn = function(templateData, cgMustache)
{
    this.fetchTemplate('select', cgMustache, function(template)
    {
        var data = {
            id: 'courier-delivery-experience_' + templateData.orderId,
            name: 'orderData[' + templateData.orderId + '][deliveryExperience]',
            class: 'required courier-delivery-experience-select',
            options: templateData.deliveryExperiences
        };
        templateData.deliveryExperienceOptions = cgMustache.renderTemplate(template, data);
    }, true);
};

CourierSpecificsDataTable.prototype.addCustomSelectToInsuranceOptionsColumn = function(templateData, cgMustache)
{
    var optionsObject = this.convertDataToSelectTemplateFormat(templateData.insuranceOptions);
    this.fetchTemplate('select', cgMustache, function(template)
    {
        var data = {
            id: 'courier-package-insurance-options_' + templateData.orderId,
            name: 'orderData[' + templateData.orderId + '][insuranceOptions]',
            class: 'courier-package-insurance-options-select',
            options: []
        };
        for (var value in optionsObject.options) {
            data.options.push({
                title: optionsObject.options[value].title,
                value: value,
                selected: (value == optionsObject.selected)
            });
        }
        templateData.packageInsuranceOptions = cgMustache.renderTemplate(template, data);
    }, true);
};

CourierSpecificsDataTable.prototype.disableInputsForCreatedLabels = function()
{
    this.getDataTable().on('fnRowCallback', function(event, nRow, aData)
    {
        if (aData.labelStatus == '' || aData.labelStatus == 'cancelled' || aData.labelStatus == 'exported' || aData.labelStatus == 'rates fetched') {
            return;
        }
        $('input, .custom-select', nRow).attr('disabled', 'disabled').addClass('disabled');
    });
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

CourierSpecificsDataTable.getActionsFromLabelStatus = function(labelStatus, exportable, cancellable, dispatchable, rateable, creatable)
{
    var actions = this.labelStatusActions[labelStatus];
    if (actions['create'] && exportable) {
        delete actions['create'];
    }
    if (actions['export'] && !exportable) {
        delete actions['export'];
    }
    if (actions['cancel'] && !cancellable) {
        delete actions['cancel'];
    }
    if (actions['dispatch'] && !dispatchable) {
        delete actions['dispatch'];
    }
    if (actions['fetchrates'] && !rateable) {
        delete actions['fetchrates'];
    }
    if (actions['create'] && !creatable) {
        delete actions['create'];
    }
    if (actions['fetchrates'] && creatable) {
        delete actions['fetchrates'];
    }
    return actions;
};

CourierSpecificsDataTable.prototype.setBulkActionButtons = function()
{
    $(CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS).hide();
    var actions = this.distinctStatusActions;
    // If there's items still left to be created then only show pre-creation actions
    if (actions.create) {
        var createActions = {"create": true};
        if (actions.fetchrates) {
            createActions.fetchrates = true;
        }
        actions = createActions;
    }
    for (var action in actions) {
        $('#' + action + CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_SUFFIX).show();
    }
    $(CourierSpecificsDataTable.SELECTOR_BULK_ACTIONS_CONTAINER).show();
    return this;
};

CourierSpecificsDataTable.elementToHtmlString = function(element)
{
    // Easiest way: add to a temporary element then get its innerHTML
    return $('<div>').append(element).html();
};

CourierSpecificsDataTable.getActionsAvailabilityFromLabelStatus = function(orderId, labelStatus)
{
    var labelStatusSelector = CourierSpecificsDataTable.SELECTOR_ORDER_LABEL_STATUS_TPL.replace('_orderId_', orderId);
    $(labelStatusSelector).val(labelStatus);
    var exportableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_EXPORTABLE_TPL.replace('_orderId_', orderId);
    var exportable = parseInt($(exportableSelector).val());
    var cancellableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_CANCELLABLE_TPL.replace('_orderId_', orderId);
    var cancellable = parseInt($(cancellableSelector).val());
    var dispatchableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_DISPATCHABLE_TPL.replace('_orderId_', orderId);
    var dispatchable = parseInt($(dispatchableSelector).val());
    var rateableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_RATEABLE_TPL.replace('_orderId_', orderId);
    var rateable = parseInt($(rateableSelector).val());
    var creatableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_CREATABLE_TPL.replace('_orderId_', orderId);
    var creatable = parseInt($(creatableSelector).val());

    return {
        exportable: exportable,
        cancellable: cancellable,
        dispatchable: dispatchable,
        rateable: rateable,
        creatable: creatable
    };
};

CourierSpecificsDataTable.prototype.resetOrderLabelStatus = function(orderId, labelStatus)
{
    var labelStatus = labelStatus || CourierSpecificsDataTable.LABEL_STATUS_DEFAULT;

    var actionAvailability = CourierSpecificsDataTable.getActionsAvailabilityFromLabelStatus(orderId, labelStatus);

    var actionsForOrder = CourierSpecificsDataTable.getActionsFromLabelStatus(
        labelStatus, actionAvailability.exportable, actionAvailability.cancellable, actionAvailability.dispatchable, actionAvailability.rateable, actionAvailability.creatable
    );

    var actionHtml = CourierSpecificsDataTable.getButtonsHtmlForActions(actionsForOrder, orderId);
    $(CourierSpecificsDataTable.SELECTOR_ACTIONS_PREFIX + orderId).html(actionHtml);
};

CourierSpecificsDataTable.prototype.listenForDimensionsChange = function()
{
    var self = this;
    $(document).on("change", CourierSpecificsDataTable.SELECTOR_COURIER_ORDER_DIMENSIONS, function() {
        var orderId = self.getOrderIdForParcelInput(this);
        if (orderId === null) {
            return;
        }
        var creatableSelector = CourierSpecificsDataTable.SELECTOR_ORDER_CREATABLE_TPL.replace('_orderId_', orderId);
        $(creatableSelector).val(0);
        var actionAvailability = CourierSpecificsDataTable.getActionsAvailabilityFromLabelStatus(orderId, '');
        if (actionAvailability.rateable) {
            self.resetOrderLabelStatus(orderId, CourierSpecificsDataTable.LABEL_STATUS_CANCELLED);
            self.setBulkActionButtons();
            $(CourierSpecificsDataTable.SELECTOR_FETCH_ALL_RATES_BUTTON).show();
            $(CourierSpecificsDataTable.SELECTOR_CREATE_ALL_LABELS_BUTTON).hide();
        }
    });
    return this;
};

CourierSpecificsDataTable.prototype.getOrderIdForParcelInput = function(element)
{
    return element.name.split(/[\[\]]/)[1];
};

CourierSpecificsDataTable.prototype.convertDataToSelectTemplateFormat = function(options)
{
    var optionsObject = {
        options: {}
    };
    var selected;
    if (options instanceof Array) {
        options.forEach(function (value) {
            optionsObject.options[value] = {title: value};
        });
    } else {
        optionsObject.options = options;
    }

    var firstValue = '';
    var selectedValue = '';

    for (var value in optionsObject.options) {
        var option = optionsObject.options[value];
        if (typeof(option) !== 'object') {
            optionsObject.options[value] = {'title': option};
        } else if (!option.hasOwnProperty('title')) {
            optionsObject.options[value].title = value;
        }
        firstValue = firstValue || value;
        if (option.hasOwnProperty('selected') && options[value].selected) {
            selectedValue = value;
        }
    }

    if (!optionsObject.selected) {
        optionsObject.selected = selectedValue || firstValue;

    }

    return optionsObject;
};