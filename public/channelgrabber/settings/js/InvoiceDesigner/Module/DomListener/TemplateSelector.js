define(['module', 'InvoiceDesigner/Module/DomListenerAbstract'], function(requireModule, DomListenerAbstract)
{
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
        DomListenerAbstract.prototype.init.call(this, module);

        /*
         * TODO (CGIV-2002): foreach event add a listener that calls back to module
         */
    };

    return new TemplateSelector();
});