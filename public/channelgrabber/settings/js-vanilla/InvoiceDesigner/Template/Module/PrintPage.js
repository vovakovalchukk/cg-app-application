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
    let PrintPage = function() {
        ModuleAbstract.call(this);
        let storage = printPageStorage;
        let marginIndicatorElement = null;

        let visibility = false;

        let margins = {
            top: null,
            bottom: null,
            left: null,
            right: null
        };

        let dimensions = {
            height: null,
            width: null
        };

        let measurement = 'mm';

        this.setDomListener(new PrintPageListener());

        this.getStorage = function() {
            return storage;
        };

        this.setMarginIndicatorElement = function(element) {
            marginIndicatorElement = element;
        };

        this.getMarginIndicatorElement = function() {
            return marginIndicatorElement;
        };

        this.setMargin = function(direction, value) {
            let marginIndicatorElement = this.getMarginIndicatorElement();
            if (value < 0) {
                return;
            }
            margins[direction] = value;
            marginIndicatorElement.style[direction] = value + measurement;
        };

        this.getMargin = function(direction) {
            return margins[direction];
        };

        this.setDimension = function(dimension, value) {
            // todo - this is where you apply the px/mm change
            let marginIndicatorElement = this.getMarginIndicatorElement();

            dimensions[dimension] = value;
            marginIndicatorElement.style[dimension] = value + measurement;
        };

        this.getDimension = function(dimension) {
            return dimensions[dimension]
        };

        this.setVisibility = function(isVisible){
            visibility = isVisible;
        }
    };

    PrintPage.MODULE_SELECTOR = '#printPageModule';

    PrintPage.prototype = Object.create(ModuleAbstract.prototype);

    PrintPage.prototype.init = function(template, templateService) {
        ModuleAbstract.prototype.init.call(this, template, templateService);
        debugger;

        console.log('this before anonymous callback: ', this);

        $(document).on(domManipulator.getTemplateInitialisedEvent(), (event, template) => {
            this.initialiseMarginIndicatorElement(event, template)
        });

//        $(document).on(domManipulator.getTemplateInitialisedEvent(), (event, template) => {
//            debugger;
////
//            const paperPage = template.getPaperPage();
//            initialiseMarginIndicatorElement.call(this);
//
//            this.setDimension("height", paperPage.getHeight());
//            this.setDimension("width", paperPage.getWidth());
//
//            //todo - only do this if nothing is set
//            this.setMargin("top", 0);
//            this.setMargin("bottom", 0);
//            this.setMargin("left", 0);
//            this.setMargin("right", 0);
//
//            this.setVisibility(false);
//        });
    };

    function createMarginIndicatorElement() {
        let marginIndicatorElement = document.createElement('div');
        //todo - move some of this into css
        marginIndicatorElement.id = 'templateMarginIndicator';
        marginIndicatorElement.style.position = 'absolute';
        marginIndicatorElement.style.width = '100%';
        marginIndicatorElement.style.height = '100%';
        marginIndicatorElement.style.border = '2px dashed red';
        marginIndicatorElement.style.boxSizing = 'border-box';
        return marginIndicatorElement;
    }

    PrintPage.prototype.initialiseMarginIndicatorElement = function(event, template) {
        debugger;
        const paperPage = template.getPaperPage();
        const templatePageElementId = ElementMapperAbstract.getDomId(paperPage);

        let templatePageElement = document.getElementById(templatePageElementId);
        let marginIndicatorElement = createMarginIndicatorElement();
        templatePageElement.prepend(marginIndicatorElement);

        this.setMarginIndicatorElement(marginIndicatorElement);

        //todo - identify what properties can come from backend or not
        //
        //  this.setMarginProperties(paperPage);
        this.setDimension("height", paperPage.getHeight());
        this.setDimension("width", paperPage.getWidth());

        //todo - only do this if nothing is set
        this.setMargin("top", 0);
        this.setMargin("bottom", 0);
        this.setMargin("left", 0);
        this.setMargin("right", 0);

        this.setVisibility(false);
    };

    PrintPage.prototype.getNewDimensionValueFromMargin = function(direction,value){
        //todo - this needs rethinking completely.
        // heights should be caluclated based on other margins and not added on top of each other
        let paperPage = this.getTemplate().getPaperPage();
        if(direction === "top" || direction === "bottom"){
            return paperPage.getHeight() - this.getMargin("top") - this.getMargin("bottom");
        }
        return paperPage.getWidth() - this.getMargin["left"] - this.getMargin["right"];
    };

    PrintPage.prototype.setPrintPageMargin = function(direction, value) {
        this.setVisibility(true);
        this.setMargin(direction, value);
        let dimensionValue = this.getNewDimensionValueFromMargin(direction);
        //todo - stop this being fixed
        this.setDimension("height", dimensionValue);
    };



    return new PrintPage();
});