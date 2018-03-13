define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function AddOns(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.toggleDeliveryInstructions()
                .listenForServiceChanges()
                .listenForAddOnOptionChanges()
                .listenForAddOnSelectAll()
                .listenForAddOnClearAll();
        };
        init.call(this);
    }

    AddOns.SELECTOR_ADD_ONS_PREFIX = '#courier-add-ons_';
    AddOns.SELECTOR_ADD_ONS_CONTAINER = '.courier-add-ons-options';
    AddOns.SELECTOR_ADD_ONS_OPTION = '.custom-select-item';
    AddOns.SELECTOR_TABLE = '#datatable';
    AddOns.SELECTOR_DEL_INSTR_TH = '.deliveryInstructions-col';
    AddOns.SELECTOR_DEL_INSTR_INPUT = '.courier-order-deliveryInstructions';
    AddOns.SELECTOR_DEL_INSTR_INPUT_ID_PREFIX = '#courier-order-deliveryInstructions-';
    AddOns.SELECTOR_HIDDEN_FIELD_INDEX = 'hidden-column-index';

    AddOns.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    AddOns.prototype.addOnsRequiringDeliveryInstructions = ['Safe Place'];

    AddOns.prototype.getDataTable = function()
    {
        return $(AddOns.SELECTOR_TABLE).dataTable();
    };

    AddOns.prototype.toggleDeliveryInstructions = function()
    {
        var self = this;
        $(AddOns.SELECTOR_TABLE).on('fnDrawCallback', function()
        {
            $(AddOns.SELECTOR_DEL_INSTR_INPUT).each(function()
            {
                var input = this;
                if (input.dataset.hasOwnProperty('required') && input.dataset.required) {
                    return;
                }
                var hide = true;
                var orderId = $(input).attr('name').match(/^orderData\[(.+?)\]/)[1];
                $(input).closest('tr').find(AddOns.SELECTOR_ADD_ONS_CONTAINER + ' ' + AddOns.SELECTOR_ADD_ONS_OPTION).each(function()
                {
                    var li = this;
                    if ($(li).find('input').is(':checked') && self.addOnsRequiringDeliveryInstructions.indexOf($(li).data('value')) > -1) {
                        hide = false;
                        return false; // break
                    }
                });
                if (hide) {
                    self.hideDeliveryInstructionsForOrder(orderId);
                } else {
                    self.showDeliveryInstructionsForOrder(orderId);
                }
            });
        });
        return this;
    };

    AddOns.prototype.showDeliveryInstructionsForOrder = function(orderId)
    {
        if ($(AddOns.SELECTOR_DEL_INSTR_INPUT + ':visible').length == 0) {
            $('.courier-group-row td').each(function(){
                var newColspan = parseInt($(this).attr('colspan')) + 1;
                $(this).attr('colspan', newColspan);
            });

            var columnIndex = $('#'+AddOns.SELECTOR_HIDDEN_FIELD_INDEX).val();
            this.getDataTable().fnSetColumnVis(columnIndex, true, false);
            $(AddOns.SELECTOR_TABLE).cgDataTable('adjustTable');
        }
        $(AddOns.SELECTOR_DEL_INSTR_INPUT_ID_PREFIX + orderId).addClass('required').show();
        return this;
    };

    AddOns.prototype.hideDeliveryInstructionsForOrder = function(orderId)
    {
        $(AddOns.SELECTOR_DEL_INSTR_INPUT_ID_PREFIX + orderId).removeClass('required').hide();
        if ($(AddOns.SELECTOR_DEL_INSTR_INPUT + ':visible').length == 0) {
            $('.courier-group-row td').each(function(){
                var newColspan = parseInt($(this).attr('colspan')) - 1;
                $(this).attr('colspan', newColspan);
            });

            var columnIndex = $(AddOns.SELECTOR_DEL_INSTR_TH).index();
            if (! $('#'+AddOns.SELECTOR_HIDDEN_FIELD_INDEX).length && columnIndex > 0) {
                $('<input>').attr({
                    type: 'hidden',
                    id: AddOns.SELECTOR_HIDDEN_FIELD_INDEX,
                    name: AddOns.SELECTOR_HIDDEN_FIELD_INDEX+'-name',
                    value: columnIndex
                }).appendTo(AddOns.SELECTOR_ADD_ONS_CONTAINER);
            }
            this.getDataTable().fnSetColumnVis(columnIndex, false, false);
            $(AddOns.SELECTOR_TABLE).cgDataTable('adjustTable');
        }
        return this;
    };

    AddOns.prototype.getSelectedValue = function(orderId)
    {
        var selected = [];
        $(AddOns.SELECTOR_ADD_ONS_PREFIX + orderId + ' input').each(function()
        {
            var input = this;
            selected.push($(input).val());
        });
        return selected;
    };

    AddOns.prototype.getContainer = function(orderId)
    {
        return $(AddOns.SELECTOR_ADD_ONS_PREFIX + orderId)
            .closest(AddOns.SELECTOR_ADD_ONS_CONTAINER);
    };

    AddOns.prototype.getOptionName = function()
    {
        return 'addOns';
    };

    AddOns.prototype.renderNewOptions = function(
        cgMustache,
        template,
        orderId,
        options,
        selected,
        container
    ) {
        var data = {
            id: AddOns.SELECTOR_ADD_ONS_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][addOn]',
            emptyTitle: "No add-ons",
            searchField: false,
            options: []
        };
        for (var index in options) {
            if (!options[index].selected && selected.indexOf(options[index].title) != -1) {
                options[index].selected = true;
            }
            data.options.push(options[index]);
        }
        var html = cgMustache.renderTemplate(template, data);
        container.empty().append(html);
        return this;
    };

    AddOns.prototype.listenForAddOnOptionChanges = function()
    {
        var self = this;
        $(document).on('click', AddOns.SELECTOR_ADD_ONS_CONTAINER + ' ' + AddOns.SELECTOR_ADD_ONS_OPTION, function()
        {
            var li = this;
            self.showAdditionalFieldsForAddOn($(li).data('value'), $(li).find('input').is(':checked'), $(li).attr('id').split('_').pop());
        });
        return this;
    };

    AddOns.prototype.listenForAddOnSelectAll = function()
    {
        var self = this;
        $(document).on('all-selected', AddOns.SELECTOR_ADD_ONS_CONTAINER, function()
        {
            var select = this;
            $(select).find(AddOns.SELECTOR_ADD_ONS_OPTION).each(function()
            {
                var li = this;
                self.showAdditionalFieldsForAddOn($(li).data('value'), $(li).find('input').is(':checked'), $(li).attr('id').split('_').pop());
            });
        });
        return this;
    };

    AddOns.prototype.listenForAddOnClearAll = function()
    {
        var self = this;
        $(document).on('all-cleared', AddOns.SELECTOR_ADD_ONS_CONTAINER, function()
        {
            var select = this;
            $(select).find(AddOns.SELECTOR_ADD_ONS_OPTION).each(function()
            {
                var li = this;
                self.showAdditionalFieldsForAddOn($(li).data('value'), $(li).find('input').is(':checked'), $(li).attr('id').split('_').pop());
            });
        });
        return this;
    };

    AddOns.prototype.showAdditionalFieldsForAddOn = function(addOn, selected, orderId)
    {
        if (this.addOnsRequiringDeliveryInstructions.indexOf(addOn) == -1) {
            return;
        }
        if (selected) {
            this.showDeliveryInstructionsForOrder(orderId);
        } else {
            this.hideDeliveryInstructionsForOrder(orderId);
        }
        return this;
    };

    return AddOns;
});
