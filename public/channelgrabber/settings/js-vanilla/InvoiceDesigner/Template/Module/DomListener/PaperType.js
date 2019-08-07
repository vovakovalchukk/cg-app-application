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
    let PaperType = function()
    {
        DomListenerAbstract.call(this);
    };

    PaperType.CONTAINER_ID = 'paperTypeModule';
    PaperType.HEIGHT_ID = 'paperHeight';
    PaperType.WIDTH_ID = 'paperWidth';

    PaperType.prototype = Object.create(DomListenerAbstract.prototype);

    PaperType.prototype.init = function(module)
    {
        DomListenerAbstract.prototype.init.call(this, module);

        $(document).on('change', `#${Constants.PAPER_TYPE_DROPDOWN_ID}`, (event, selectBox, id) => {
            this.getModule().paperTypeSelectionMade(id);
        });

        $(document).on('change', `#${Constants.MEASUREMENT_UNIT_DROPDOWN_ID}`, (event, selectBox, id) => {
            this.getModule().changeMeasurementUnit(id);
        });

        $(document).on('change', `#${PaperType.HEIGHT_ID}`, (event) => {
            let desiredValue = event.target.value;
            this.getModule().changePaperDimension("height", desiredValue);
        });

        $(document).on('change', `#${PaperType.WIDTH_ID}`, (event) => {
            let desiredValue = event.target.value;
            this.getModule().changePaperDimension("width", desiredValue);
        });
    };

    return PaperType;
});