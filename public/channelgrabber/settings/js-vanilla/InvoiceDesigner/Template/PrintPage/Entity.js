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

        let data = {
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
            visibility: false
        };
        let marginIndicatorElement = null;

        this.getData = function(){
            return data;
        };

        this.setVisibilityFromData = function(data) {
            for (let direction in data.margin) {
                if (!data.margin[direction]) {
                    continue;
                }
                this.setVisibility(true);
                break;
            }
        };

        this.setVisibility = function(value){
            data.visibility = value;
        };

        this.render = function(template, templatePageElement) {
            let data = this.getData();
            this.setVisibilityFromData(data);
            let marginIndicatorElement = this.createMarginIndicatorElement(data);
            templatePageElement.prepend(marginIndicatorElement);
            this.setMarginIndicatorElement(marginIndicatorElement);

            const paperPage = template.getPaperPage();

            let storedHeightDimension = this.calculateHeightDimensionFromMargins(template);
            let storedWidthDimension = this.calculateWidthDimensionFromMargins(template);
            let height =  storedHeightDimension ? storedHeightDimension : paperPage.getHeight();
            let width =  storedWidthDimension ? storedWidthDimension : paperPage.getWidth();

            this.setDimension(template, "height", height);
            this.setDimension(template,"width", width);

            for(let margin in data.margin){
                let marginValue = data.margin[margin];
                let desiredValue = typeof marginValue === "number" ? marginValue : 0;
                this.setMargin(template, margin, desiredValue, true);
                let dimensionValue = this.getNewDimensionValueFromMargin(margin, template);
                this.setDimension(template, MARGIN_TO_DIMENSION[margin], dimensionValue);
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

            let margins = printPage.getData().margin;

            let topMargin = margins.top ? margins.top : 0;
            let bottomMargin = margins.bottom ? margins.bottom : 0;

            return paperPage.getHeight() - (topMargin + bottomMargin);
        };

        this.calculateWidthDimensionFromMargins = function(template){
            const paperPage = template.getPaperPage();
            const printPage = template.getPrintPage();

            let margins = printPage.getData().margin;

            let leftMargin = margins.left ? margins.left : 0;
            let rightMargin = margins.right ? margins.right : 0;

            return paperPage.getWidth() - (leftMargin + rightMargin)
        };

        this.setMarginIndicatorElement = function(element) {
            marginIndicatorElement = element;
        };

        this.getMarginIndicatorElement = function() {
            return marginIndicatorElement;
        };

        this.createMarginIndicatorElement = function({visibility}) {
            let marginIndicatorElement = document.createElement('div');
            marginIndicatorElement.id = 'templateMarginIndicator';
            marginIndicatorElement.className = 'template-margin-indicator-element';
            marginIndicatorElement.style.visibility = visibility ? 'visible' : 'hidden';
            return marginIndicatorElement;
        };

        this.setMargin = function(template, direction, value, populating) {
            let marginIndicatorElement = this.getMarginIndicatorElement();
            const measurementUnit = template.getPaperPage().getMeasurementUnit();

            if (value < 0) {
                return;
            }
            value = parseInt(value);

            marginIndicatorElement.style[direction] = value + measurementUnit;

            if(populating){
                data.margin[direction] = value;
                return;
            }

            let newMarginState = Object.assign({}, data.margin);
            newMarginState[direction] = value;
            this.set("margin", newMarginState);
        };

        this.setDimension = function(template, dimension, value) {
            const measurementUnit = template.getPaperPage().getMeasurementUnit();
            let marginIndicatorElement = this.getMarginIndicatorElement();
            dimension[dimension] = value;
            marginIndicatorElement.style[dimension] = value + measurementUnit;
        };

        this.getDimension = function(dimension) {
            return data.dimension[dimension]
        };

        this.setVisibility = function(isVisible){
            data.visibility = isVisible;
        };

        this.get = function(field)
        {
            return data[field];
        };

        this.set = function(field, value, populating)
        {
            data[field] = value;

            if (populating) {
                return;
            }

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.toJson = function(){
        let data = Object.assign({}, this.getData());
        delete data.visibility;
        delete data.dimension;
        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;

    function createPrototype() {
        let combinedPrototype = EntityHydrateAbstract.prototype;
        for (var key in PubSubAbstract.prototype) {
            combinedPrototype[key] = PubSubAbstract.prototype[key];
        }
        return combinedPrototype;
    }
});