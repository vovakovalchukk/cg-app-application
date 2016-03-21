define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function AddOns(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges()
                .listenForAddOnOptionChanges()
                .listenForAddOnSelectAll();
        };
        init.call(this);
    }

    AddOns.SELECTOR_ADD_ONS_PREFIX = '#courier-add-ons_';
    AddOns.SELECTOR_ADD_ONS_CONTAINER = '.courier-add-ons-options';
    AddOns.SELECTOR_ADD_ONS_OPTION = '.custom-select-item';

    AddOns.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    AddOns.prototype.mutuallyExclusiveOptions = [
        ['Insurance £50', 'Insurance £500', 'Insurance £1000', 'Insurance £2500'],
        ['Signature', 'Safe Place']
    ];

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
            name: 'orderData[' + orderId + '][addOns]',
            emptyTitle: " ",
            searchField: false,
            options: []
        };
        for (var index in options) {
            data.options.push({
                title: options[index],
                selected: (selected.indexOf(options[index]) != -1)
            });
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
            self.deSelectMutuallyExclusiveOptions(li);
        });
        return this;
    };

    AddOns.prototype.deSelectMutuallyExclusiveOptions = function(li)
    {
        if (!$(li).find('input:checkbox').is(':checked')) {
            return;
        }
        var mutuallyExclusiveOptions = this.mutuallyExclusiveOptions;
        var value =  $(li).data('value');
        for (var index in mutuallyExclusiveOptions) {
            if (mutuallyExclusiveOptions[index].indexOf(value) == -1) {
                continue;
            }

            $(li).closest('ul').find(AddOns.SELECTOR_ADD_ONS_OPTION).each(function()
            {
                if (this == li ||
                    !$(this).find('input:checkbox').is(':checked') ||
                    mutuallyExclusiveOptions[index].indexOf($(this).data('value')) == -1
                ) {
                    return true; // continue
                }
                $(this).click();
            });
        }
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
                self.deSelectMutuallyExclusiveOptions(li);
            });
        });
    };

    return AddOns;
});