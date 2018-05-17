define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function PackageType(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    PackageType.SELECTOR_PACKAGE_TYPE_PREFIX = '#courier-package-type_';
    PackageType.SELECTOR_PACKAGE_TYPE_CONTAINER = '.courier-package-type-options';

    PackageType.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    PackageType.prototype.getSelectedValue = function(orderId)
    {
        return $(PackageType.SELECTOR_PACKAGE_TYPE_PREFIX + orderId + ' input').val();
    };

    PackageType.prototype.getContainer = function(orderId)
    {
        return $(PackageType.SELECTOR_PACKAGE_TYPE_PREFIX + orderId)
            .closest(PackageType.SELECTOR_PACKAGE_TYPE_CONTAINER);
    };

    PackageType.prototype.getOptionName = function()
    {
        return 'packageTypes';
    };

    PackageType.prototype.renderNewOptions = function(
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
            id: PackageType.SELECTOR_PACKAGE_TYPE_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][packageType]',
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

    return PackageType;
});
