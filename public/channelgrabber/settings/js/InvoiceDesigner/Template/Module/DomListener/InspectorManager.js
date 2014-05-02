define(['../DomListenerAbstract', 'jQuery'], function(DomListenerAbstract, $)
{
    var InspectorManager = function()
    {
        DomListenerAbstract.call(this);
    };

    InspectorManager.prototype = Object.create(DomListenerAbstract.prototype);

    InspectorManager.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);
        this.initListeners();
    };

    InspectorManager.prototype.initListeners = function()
    {
        var self = this;
        $(document).on('invoice-template-element-selected', function(event, element)
        {
            self.getModule().elementSelected(element);
        });
    };

    return new InspectorManager();
});