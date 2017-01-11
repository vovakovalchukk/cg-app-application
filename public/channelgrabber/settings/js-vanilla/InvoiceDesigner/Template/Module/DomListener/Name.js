define([
    'InvoiceDesigner/Module/DomListenerAbstract',
    'jquery'
], function(
    DomListenerAbstract,
    $
) {

    var Name = function()
    {
        DomListenerAbstract.call(this);
    };

    Name.TEMPLATE_NAME_SELECTOR = '#template-name';
    Name.TEMPLATE_NAME_CONTAINER_SELECTOR = '#template-name-container';

    Name.prototype = Object.create(DomListenerAbstract.prototype);

    Name.prototype.init = function(module)
    {
        var self = this;
        var renderTimeout;
        DomListenerAbstract.prototype.init.call(this, module);
        $(Name.TEMPLATE_NAME_SELECTOR).off('keyup paste input change keypress').on('keyup paste input change keypress', function() {
            var name = $(this).val();
            clearTimeout(renderTimeout);
            renderTimeout = setTimeout(function() {
                self.getModule().updateName(name);
            }, 500);
        });
    };

    Name.prototype.getTemplateNameContainerSelector = function()
    {
        return Name.TEMPLATE_NAME_CONTAINER_SELECTOR;
    };

    Name.prototype.getTemplateNameSelector = function()
    {
        return Name.TEMPLATE_NAME_SELECTOR;
    };

    return new Name();
});