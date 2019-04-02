define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function InsuranceOption(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    InsuranceOption.SELECTOR_INSURANCE_OPTION_PREFIX = '#courier-package-insurance-options_';
    InsuranceOption.SELECTOR_INSURACE_OPTION_CONTAINER = '.courier-package-insurance-options';

    InsuranceOption.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    InsuranceOption.prototype.getSelectedValue = function(orderId)
    {
        return $(InsuranceOption.SELECTOR_INSURANCE_OPTION_PREFIX + orderId + ' input').val();
    };

    InsuranceOption.prototype.getContainer = function(orderId)
    {
        return $(InsuranceOption.SELECTOR_INSURANCE_OPTION_PREFIX + orderId)
            .closest(InsuranceOption.SELECTOR_INSURACE_OPTION_CONTAINER);
    };

    InsuranceOption.prototype.getOptionName = function()
    {
        return 'insuranceOptions';
    };

    InsuranceOption.prototype.renderNewOptions = function(
        cgMustache,
        template,
        orderId,
        options,
        selected,
        container
    ) {
        var optionsObject = {};
        if (options instanceof Array) {
            options.forEach(function (value) {
                optionsObject[value] = {title: value};
            });
        } else {
            optionsObject = options;
        }

        var firstValue = '';
        var selectedValue = '';

        for (var value in optionsObject) {
            var option = optionsObject[value];
            if (typeof(option) !== 'object') {
                optionsObject[value] = {'title': option};
            } else if (!option.hasOwnProperty('title')) {
                optionsObject[value].title = value;
            }
            firstValue = firstValue || value;
            if (option.hasOwnProperty('selected') && options[value].selected) {
                selectedValue = value;
            }
        }

        if (!optionsObject[selected]) {
            selected = selectedValue || firstValue;
        }

        var data = {
            id: InsuranceOption.SELECTOR_INSURANCE_OPTION_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][InsuranceOption]',
            class: 'required',
            options: []
        };
        for (var value in optionsObject) {
            data.options.push({
                title: optionsObject[value].title,
                value: value,
                selected: (value == selected)
            });
        }
        var html = cgMustache.renderTemplate(template, data);
        container.empty().append(html);
        return this;
    };
    return InsuranceOption;
});
