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

    PackageType.SELECTOR_PACKAGE_TYPE_PREFIX = '.courier-package-type_';
    PackageType.SELECTOR_PACKAGE_TYPE_CONTAINER = '.courier-package-type-options';
    PackageType.SELECTOR_ORDER_LABEL_STATUS_TPL = '#datatable input[name="orderInfo[_orderId_][labelStatus]"]';

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

        container.each(function (index, element) {
            let parcelNumber = index + 1;
            let data = {
                id: PackageType.SELECTOR_PACKAGE_TYPE_PREFIX.replace('.', '') + orderId + '-' + parcelNumber,
                name: 'parcelData[' + orderId + '][' + parcelNumber + '][packageType]',
                class: 'required courier-package-type_' + orderId,
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
            $(element).empty().append(html);
        });
        return this;
    };

    PackageType.prototype.preventUpdateOptions = function(orderId)
    {
        var labelStatusSelector = PackageType.SELECTOR_ORDER_LABEL_STATUS_TPL.replace('_orderId_', orderId);
        if ($(labelStatusSelector).val() === 'rates fetched') {
            return true;
        }
        return false;
    };

    return PackageType;
});
