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
    PaperType.DROPDOWN_ID = 'paperTypeDropdown';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);

        $(document).on(CustomSelect.EVENT_SELECT_CHANGED, function (event, selectBox, selectedId) { // TODO Blocked by CGIV-2002. Event implemented in there
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(selectedId, isInverse);
        });

        $("#" + PaperType.CHECKBOX_ID).click(function() {
            var selectedId = $("#" + PaperType.CONTAINER_ID + " input[type=hidden]").val();
            var isInverse = $("#" + PaperType.CHECKBOX_ID).is(":checked");
            self.getModule().selectionMade(selectedId, isInverse);
        });
    };

    return PaperType;
});