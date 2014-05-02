define(['InvoiceDesigner/Module/DomListenerAbstract'], function(AppModuleDomListenerAbstract)
{
    var DomListenerAbstract = function()
    {
        AppModuleDomListenerAbstract.call(this);
    };

    DomListenerAbstract.prototype = Object.create(AppModuleDomListenerAbstract.prototype);

    return DomListenerAbstract;
});