define([
    'module',
    'InvoiceDesigner/Module/DomListenerAbstract',
    'element/customSelect',
    'InvoiceDesigner/Constants'
], function(
    requireModule,
    DomListenerAbstract,
    CustomSelect,
    Constants
) {

    var PaperType = function()
    {
        DomListenerAbstract.call(this);
    };

    PaperType.CONTAINER_ID = 'paperTypeModule';
    PaperType.HEIGHT_ID = 'paperHeight';
    PaperType.WIDTH_ID = 'paperWidth';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        var self = this;
        DomListenerAbstract.prototype.init.call(this, module);

        $(document).on('change', `#${Constants.PAPER_TYPE_DROPDOWN_ID}`, function (event, selectBox, id) {
            self.getModule().paperTypeSelectionMade(id);
        });

        $(document).on('change', `#${Constants.MEASUREMENT_UNIT_DROPDOWN_ID}`, function (event, selectBox, id) {
            self.getModule().changeMeasurementUnit(id);
        });

        $(document).on('change', `#${PaperType.HEIGHT_ID}`, (event) => {
           let desiredValue = event.target.value;
           self.getModule().changePaperDimension("height", desiredValue);
        });

        $(document).on('change', `#${PaperType.WIDTH_ID}`, (event) => {
           let desiredValue = event.target.value;
           self.getModule().changePaperDimension("width", desiredValue);
        });

        $("#" + PaperType.CHECKBOX_ID).click(function() {
            var selectedId = $("#" + PaperType.CONTAINER_ID + " input[type=hidden]").val();
            self.getModule().paperTypeSelectionMade(selectedId);
        });
    };

    return PaperType;
});