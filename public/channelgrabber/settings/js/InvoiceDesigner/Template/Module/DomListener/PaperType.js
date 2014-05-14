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

    PaperType.ID = 'paperTypeModule';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);

        //$(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, id) { // TODO Blocked by CGIV-2002. Implemented in there
        $(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, id) {
            console.log("CLICK");
            var isInverse = $("#" + PaperType.ID + " #inverseLabelPosition").is(":checked");
            self.getModule().selectionMade(id, isInverse);
        });
    };

    return new PaperType();
});