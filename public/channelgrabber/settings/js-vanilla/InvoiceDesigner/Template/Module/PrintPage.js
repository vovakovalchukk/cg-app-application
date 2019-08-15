define([
    'InvoiceDesigner/Template/ModuleAbstract',
    'InvoiceDesigner/Template/Module/DomListener/PrintPage',
    'InvoiceDesigner/Template/PaperType/Entity',
    'InvoiceDesigner/Template/Element/MapperAbstract',
    'InvoiceDesigner/Template/PrintPage/Storage/Ajax',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    ModuleAbstract,
    PrintPageListener,
    PaperType,
    ElementMapperAbstract,
    printPageStorage,
    domManipulator
) {
    const MARGIN_TO_DIMENSION = {
        top: 'height',
        bottom: 'height',
        left: 'width',
        right: 'width'
    };

    let PrintPage = function() {
        ModuleAbstract.call(this);
        let storage = printPageStorage;

        this.setDomListener(new PrintPageListener());

        this.getStorage = function() {
            return storage;
        };
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

    PrintPage.prototype.setPrintPageMargin = function(direction, value, populating) {
        const template = this.getTemplate();
        const printPage = template.getPrintPage();

        printPage.setMargin(template, direction, value, populating);

        let dimensionValue = printPage.getNewDimensionValueFromMargin(direction, template);

        printPage.setDimension(template, MARGIN_TO_DIMENSION[direction], dimensionValue);
    };

    return new PrintPage();

});