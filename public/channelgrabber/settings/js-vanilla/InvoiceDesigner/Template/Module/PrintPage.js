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
        $(document).on(domManipulator.getTemplateInitialisedEvent(), (event, template) => {
            this.initialiseMarginIndicatorElement(event, template)
        });

        this.initialiseMarginInputs(template);
    };

    PrintPage.prototype.initialiseMarginIndicatorElement = function(event, template) {
        const paperPage = template.getPaperPage();
        const printPage = template.getPrintPage();

        const templatePageElementId = ElementMapperAbstract.getDomId(paperPage);

        let templatePageElement = document.getElementById(templatePageElementId);
        let marginIndicatorElement = createMarginIndicatorElement();
        templatePageElement.prepend(marginIndicatorElement);

        printPage.setMarginIndicatorElement(marginIndicatorElement);

        printPage.setDimension("height", paperPage.getHeight());
        printPage.setDimension("width", paperPage.getWidth());

        this.initialiseMargins(printPage);

        printPage.setVisibility(false);
    };

    PrintPage.prototype.initialiseMarginInputs = function(template) {
        let printPage = template.getPrintPage();
        let marginValues = printPage.getState().margin;
        let inputs = this.getDomListener().getInputs();
        for(let marginDirection in marginValues){
            domManipulator.setMarginValueToInput(inputs[marginDirection], marginValues[marginDirection]);
        }
    };

    PrintPage.prototype.initialiseMargins = function(printPage){
        let state = printPage.getState();
        for(let margin in state.margin){
            let marginValue = state.margin[margin];
            let desiredValue = typeof marginValue === "number" ? marginValue : 0;
            this.setPrintPageMargin(margin, desiredValue);
        }
    };

    PrintPage.prototype.getNewDimensionValueFromMargin = function(direction, value){
        const template = this.getTemplate();
        const paperPage = template.getPaperPage();
        const printPage = template.getPrintPage();
        if(MARGIN_TO_DIMENSION[direction] === 'height'){
            return paperPage.getHeight() - (printPage.getMargin("top") + printPage.getMargin("bottom"));
        }
        return paperPage.getWidth() - (printPage.getMargin("left") + printPage.getMargin("right"));
    };

    PrintPage.prototype.setPrintPageMargin = function(direction, value) {
        const template = this.getTemplate();
        const printPage = template.getPrintPage();

        printPage.setVisibility(true);

        debugger;

        printPage.setMargin(direction, value);

        let dimensionValue = this.getNewDimensionValueFromMargin(direction, value);

        printPage.setDimension(MARGIN_TO_DIMENSION[direction], dimensionValue);
    };

    return new PrintPage();

    function createMarginIndicatorElement() {
        let marginIndicatorElement = document.createElement('div');
        //todo - move some of this into css
        marginIndicatorElement.id = 'templateMarginIndicator';
        marginIndicatorElement.style.position = 'absolute';
        marginIndicatorElement.style.width = '100%';
        marginIndicatorElement.style.height = '100%';
        marginIndicatorElement.style.border = '2px dashed red';
        marginIndicatorElement.style.boxSizing = 'border-box';
        marginIndicatorElement.style.zIndex = 100;
        return marginIndicatorElement;
    }
});