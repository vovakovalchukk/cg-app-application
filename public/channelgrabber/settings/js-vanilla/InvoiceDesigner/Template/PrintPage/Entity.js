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

    const Entity = function() {
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

        this.render = function(template, templatePageElement) {
            console.log('in render PrintPage (is this called more than once? I imagine 4 times due to loop)');


            let data = this.getData();
            this.setVisibilityFromData(data);
            let marginIndicatorElement = this.createMarginIndicatorElement(template, data);
            templatePageElement.prepend(marginIndicatorElement);
            this.setMarginIndicatorElement(marginIndicatorElement);

//            const paperPage = template.getPaperPage();

            // a lot of this needs to be reworked. Needs to only be concerned with rendering the indicator element. the createMarginIndicator method could cover most of this

//            let heightDimensionFromMargins = this.calculateHeightDimensionFromMargins(template);
//            let widthDimensionFromMargins = this.calculateWidthDimensionFromMargins(template);
//            let height = heightDimensionFromMargins ? heightDimensionFromMargins : paperPage.getHeight();
//            let width = widthDimensionFromMargins ? widthDimensionFromMargins : paperPage.getWidth();

            this.setDimensionsFromMargins(data, template);

            this.setVisibility(false);
        };

        this.setDimensionsFromMargins = function(data, template) {
            //
            for (let margin in data.margin) {
                // need to see if we can scrap the setMargins... doesn't feel right instantiating in the render method.
//                let marginValue = data.margin[margin];
//                let desiredValue = typeof marginValue === "number" ? marginValue : 0;
//                this.setMargin(template, margin, desiredValue, true);
                let dimensionValue = this.getNewDimensionValueFromMargin(margin, template);
                this.setDimension(template, MARGIN_TO_DIMENSION[margin], dimensionValue);
            }
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

        this.createMarginIndicatorElement = function(template, {visibility}) {
            let marginIndicatorElement = document.createElement('div');

            const measurementUnit = template.getPaperPage().getMeasurementUnit();

            marginIndicatorElement.id = 'templateMarginIndicator';
            marginIndicatorElement.className = 'template-margin-indicator-element';
            marginIndicatorElement.style.visibility = visibility ? 'visible' : 'hidden';

            for (let margin in data.margin) {
                // need to see if we can scrap the setMargins... doesn't feel right instantiating in the render method.
                let marginValue = data.margin[margin];
                let desiredValue = typeof marginValue === "number" ? marginValue : 0;
                if (desiredValue < 0) {
                    continue;
                }
//                desiredValue = parseInt(desiredValue);
                marginIndicatorElement.style[margin] = desiredValue + measurementUnit;
            }
//            let marginIndicatorElement = this.getMarginIndicatorElement();
//            const measurementUnit = template.getPaperPage().getMeasurementUnit();
//
//            if (value < 0) {
//                return;
//            }
//            value = parseInt(value);
//
//            marginIndicatorElement.style[direction] = value + measurementUnit;
//
//            if(populating){
//                data.margin[direction] = value;
//                return;
//            }
//
//            let newMarginState = Object.assign({}, data.margin);
//            newMarginState[direction] = value;
//            this.set("margin", newMarginState);



            return marginIndicatorElement;
        };

        this.getMargin = function(direction){
            return data['margin'][direction];
        };

        this.setMargin = function(template, direction, value, populating) {
            // todo - purge a lot of this
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
            debugger;
            const measurementUnit = template.getPaperPage().getMeasurementUnit();
            let marginIndicatorElement = this.getMarginIndicatorElement();
            dimension[dimension] = value;
            if(!marginIndicatorElement){
                return;
            }
            marginIndicatorElement.style[dimension] = value + measurementUnit;
        };

        this.getDimension = function(dimension) {
            return data.dimension[dimension]
        };

        this.getHeight = function() {
            return data.height;
        };

        this.getWidth = function() {
            return data.width;
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