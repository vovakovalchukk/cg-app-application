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
        if (options instanceof Array) {
            var optionsObject = {};
            for (var index in options) {
                optionsObject[options[index]] = options[index];
            }
            options = optionsObject;
        }
        if (!options[selected]) {
            for (var value in options) {
                selected = value;
                break;
            }
        }
        var data = {
            id: PackageType.SELECTOR_PACKAGE_TYPE_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][packageType]',
            class: 'required',
            options: []
        };
        for (var value in options) {
            data.options.push({
                title: options[value],
                value: value,
                selected: (options[value] == selected)
            });
        }
        var html = cgMustache.renderTemplate(template, data);
        container.empty().append(html);
        return this;
    };

    return PackageType;
});