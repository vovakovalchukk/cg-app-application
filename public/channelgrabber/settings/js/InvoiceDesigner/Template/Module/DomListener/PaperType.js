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

    PaperType.CONTAINER_ID = 'paperTypeModule';
    PaperType.CHECKBOX_ID = 'inverseLabelPosition';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);
        console.log("listening: " + paperTypeDropdownId + " ("+$(paperTypeDropdownId).length+")");
        $(document).on('change', paperTypeDropdownId, function (event, selectBox, id) {
            console.log("Dropdown changed");
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(id, isInverse);
        });

        $("#" + PaperType.CHECKBOX_ID).click(function() {
            var selectedId = $("#" + PaperType.CONTAINER_ID + " input[type=hidden]").val();
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(selectedId, isInverse);
        });
    };

    return PaperType;
});