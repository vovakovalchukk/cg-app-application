define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract',
    'element/customSelect'
], function(
    requireModule,
    DomListenerAbstract,
    CustomSelect
    ) {

    var PaperType = function()
    {
        DomListenerAbstract.call(this);

        var events = requireModule.config().events;

        this.getEvents = function()
        {
            return events;
        };
    };

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        console.log("Init DomListener")
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);

        $(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, id) { // TODO Blocked by CGIV-2002. Implemented in there
            self.getModule().selectionMade(id);
        });
    };

    return new PaperType();
});