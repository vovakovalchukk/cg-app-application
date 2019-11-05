define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PrintPage',
    'InvoiceDesigner/Template/PaperType/Entity',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/DomManipulator',
    'InvoiceDesigner/Constants'
], function(
    ModuleAbstract,
    PrintPageListener,
    PaperType,
    ElementMapperAbstract,
    domManipulator,
    Constants
) {
    let PrintPage = function() {
        ModuleAbstract.call(this);
        this.setDomListener(new PrintPageListener());
    };

    PrintPage.MODULE_SELECTOR = '#printPageModule';

    PrintPage.prototype = Object.create(ModuleAbstract.prototype);

    PrintPage.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        this.initialiseMarginInputs(template);
    };

    PrintPage.prototype.initialiseMarginInputs = function(template) {
        let printPage = template.getPrintPage();
        let marginValues = printPage.getData().margin;
        let inputs = this.getDomListener().getInputs();
        for (let marginDirection in marginValues) {
            let value = marginValues[marginDirection];
            let input = inputs[marginDirection];
            if (!value || !input) {
                continue;
            }
            domManipulator.setValueToInput(inputs[marginDirection], marginValues[marginDirection]);
        }
    };

    PrintPage.prototype.setPrintPageMargin = function(direction, value) {
        const template = this.getTemplate();
        const printPage = template.getPrintPage();
        const multiPage = template.getMultiPage();

        printPage.setMargin(template, direction, value);

        let dimension = Constants.MARGIN_TO_DIMENSION[direction];
        let gridTrack = Constants.DIMENSION_TO_GRID_TRACK[dimension];
        let gridTrackValue = multiPage.getGridTrack(gridTrack);
        let maxValue = multiPage.calculateMaxDimensionValue(template, dimension, gridTrackValue);
        multiPage.setDimension(dimension, maxValue);
    };

    return new PrintPage();
});
