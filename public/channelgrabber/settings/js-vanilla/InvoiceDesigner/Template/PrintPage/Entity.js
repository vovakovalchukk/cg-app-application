define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {

    const MARGIN_TO_DIMENSION = {
        top: 'height',
        bottom: 'height',
        left: 'width',
        right: 'width'
    };

    let Entity = function() {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

        let state = {
            margin: {
                top: null,
                bottom: null,
                left: null,
                right: null
            },
            dimension: {
                height: null,
                width: null
            },
            measurement: 'mm',
            visibility: false
        };
        let marginIndicatorElement = null;

        this.getState = function(){
            return state;
        };

        this.render = function(template, templatePageElement) {
            let marginIndicatorElement = this.createMarginIndicatorElement();
            templatePageElement.prepend(marginIndicatorElement);
            this.setMarginIndicatorElement(marginIndicatorElement);

            const paperPage = template.getPaperPage();

            let storedHeightDimension = this.calculateHeightDimensionFromMargins(template);
            let storedWidthDimension = this.calculateWidthDimensionFromMargins(template);
            let height =  storedHeightDimension ? storedHeightDimension : paperPage.getHeight();
            let width =  storedWidthDimension ? storedWidthDimension : paperPage.getWidth();

            this.setDimension("height", height);
            this.setDimension("width", width);

            let state = this.getState();
            for(let margin in state.margin){
                let marginValue = state.margin[margin];
                let desiredValue = typeof marginValue === "number" ? marginValue : 0;
                //initialise single margin
                this.setVisibility(true);
                this.setMargin(margin, desiredValue, true);
                let dimensionValue = this.getNewDimensionValueFromMargin(margin, template);
                this.setDimension(MARGIN_TO_DIMENSION[margin], dimensionValue);
            }

            this.setVisibility(false);
        };

        this.getNewDimensionValueFromMargin = function(direction, template){
            if(MARGIN_TO_DIMENSION[direction] === 'height'){
                return this.calculateHeightDimensionFromMargins(template);
            }
            return this.calculateWidthDimensionFromMargins(template);
        };

        this.calculateHeightDimensionFromMargins = function(template){
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();
            return paperPage.getHeight() - (printPage.getMargin("top") + printPage.getMargin("bottom"));
        };

        this.calculateWidthDimensionFromMargins = function(template){
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();
            return paperPage.getWidth() - (printPage.getMargin("left") + printPage.getMargin("right"))
        };

        this.setMarginIndicatorElement = function(element) {
            marginIndicatorElement = element;
        };

        this.getMarginIndicatorElement = function() {
            return marginIndicatorElement;
        };

        this.createMarginIndicatorElement = function() {
            let marginIndicatorElement = document.createElement('div');
            marginIndicatorElement.id = 'templateMarginIndicator';
            marginIndicatorElement.style.position = 'absolute';
            marginIndicatorElement.style.width = '100%';
            marginIndicatorElement.style.height = '100%';
            marginIndicatorElement.style.border = '2px dashed red';
            marginIndicatorElement.style.boxSizing = 'border-box';
            marginIndicatorElement.style.zIndex = 100;
            return marginIndicatorElement;
        };

        this.setMargin = function(direction, value, populating) {
            let marginIndicatorElement = this.getMarginIndicatorElement();
            if (value < 0) {
                return;
            }
            value = parseInt(value);

            marginIndicatorElement.style[direction] = value + state.measurement;

            if(populating){
                state.margin[direction] = value;
                return;
            }

            let newMarginState = Object.assign({}, state.margin);
            newMarginState[direction] = value;
            this.set("margin", newMarginState);
        };

        this.getMargin = function(direction) {
            return state.margin[direction];
        };

        this.setDimension = function(dimension, value) {
            // todo - this is where you apply the px/mm change
            let marginIndicatorElement = this.getMarginIndicatorElement();

            dimension[dimension] = value;
            marginIndicatorElement.style[dimension] = value + state.measurement;
        };

        this.getDimension = function(dimension) {
            return state.dimension[dimension]
        };

        this.setVisibility = function(isVisible){
            state.visibility = isVisible;
        };

        this.get = function(field)
        {
            return state[field];
        };

        this.set = function(field, value, populating)
        {
            state[field] = value;
//
            if (populating) {
                return;
            }

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    return Entity;

    function createPrototype() {
        let combinedPrototype = EntityHydrateAbstract.prototype;
        for (var key in PubSubAbstract.prototype) {
            combinedPrototype[key] = PubSubAbstract.prototype[key];
        }
        return combinedPrototype;
    }
});