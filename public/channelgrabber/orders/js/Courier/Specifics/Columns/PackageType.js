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
        var data = {
            id: PackageType.SELECTOR_PACKAGE_TYPE_PREFIX.replace('#', '') + orderId,
            name: 'orderData[' + orderId + '][packageType]',
            class: 'required',
            options: []
        };
        for (var index in options) {
            data.options.push({
                title: options[index],
                selected: (options[index] == selected)
            });
        }
        var html = cgMustache.renderTemplate(template, data);
        container.empty().append(html);
        return this;
    };

    return PackageType;
});