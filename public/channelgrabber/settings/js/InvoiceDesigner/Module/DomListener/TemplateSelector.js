define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract',
    'element/customSelect'
], function(
    requireModule,
    DomListenerAbstract,
    CustomSelect
) {

    var TemplateSelector = function()
    {
        DomListenerAbstract.call(this);

        var events = requireModule.config().events;

        this.getEvents = function()
        {
            return events;
        };
    };

    TemplateSelector.prototype = Object.create(DomListenerAbstract.prototype);

    TemplateSelector.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        $(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, id) {
            self.getModule().selectionMade(id);
        });
        $(this.DUPLICATE_TEMPLATE_SELECTOR).click(function () {
            if (this.hasClass('disabled'))  {
                return;
            }

            self.getModule().duplicate();
        });
    };

    TemplateSelector.prototype.enableDuplicate()
    {
        $(this.DUPLICATE_TEMPLATE_SELECTOR).removeClass('disabled');
    };

    TemplateSelector.prototype.DUPLICATE_TEMPLATE_SELECTOR = '#duplicate-template';
    TemplateSelector.prototype.NEW_TEMPLATE_SELECTOR = '#new-template';

    return new TemplateSelector();
});