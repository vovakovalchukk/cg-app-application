define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function AddOns(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    AddOns.SELECTOR_ADD_ONS_PREFIX = '#courier-add-ons_';
    AddOns.SELECTOR_ADD_ONS_CONTAINER = '.courier-add-ons-options';

    AddOns.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

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

    return AddOns;
});