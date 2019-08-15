define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract'
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

        this.getData = function() {
            return data;
        };

        this.render = function(template, templatePageElement) {
            let data = this.getData();
            this.setVisibilityFromData(data);
            this.renderMarginIndicatorElement(template, data, templatePageElement);
        };

        this.renderMarginIndicatorElement = function(template, data, templatePageElement) {
            let marginIndicatorElement = this.createMarginIndicatorElement(template, data);
            templatePageElement.prepend(marginIndicatorElement);
            this.setMarginIndicatorElement(marginIndicatorElement);
        };

        this.setVisibilityFromData = function(data) {
            for (let direction in this.getMargins()) {
                if (!this.getMargin(direction)) {
                    continue;
                }
                this.setVisibility(true);
                break;
            }
        };

        this.setVisibility = function(value) {
            data.visibility = value;
        };

        this.getNewDimensionValueFromMargin = function(direction, template) {
            let margins = template.getPrintPage().getMargins();

            if (MARGIN_TO_DIMENSION[direction] === 'height') {
                return this.calculateHeightDimensionFromMargins(template, margins);
            }
            return this.calculateWidthDimensionFromMargins(template, margins);
        };

        this.calculateHeightDimensionFromMargins = function(template, margins) {
            const paperPage = template.getPaperPage();

            if(!margins){
                return paperPage.getHeight();
            }

            let topMargin = margins.top ? margins.top : 0;
            let bottomMargin = margins.bottom ? margins.bottom : 0;

            return paperPage.getHeight() - (topMargin + bottomMargin);
        };

        this.calculateWidthDimensionFromMargins = function(template, margins) {
            const paperPage = template.getPaperPage();

            if(!margins){
                return paperPage.getWidth();
            }

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
            const marginIndicatorElement = document.createElement('div');
            const measurementUnit = template.getPaperPage().getMeasurementUnit();

            marginIndicatorElement.id = 'templateMarginIndicator';
            marginIndicatorElement.className = 'template-margin-indicator-element';
            marginIndicatorElement.style.visibility = visibility ? 'visible' : 'hidden';
            marginIndicatorElement.style.height = this.getHeight() + measurementUnit;
            marginIndicatorElement.style.width = this.getWidth() + measurementUnit;

            if(!data.margin){
                return marginIndicatorElement;
            }

            for (let margin in this.getMargins()) {
                let marginValue = this.getMargin(margin);
                let desiredValue = typeof marginValue === "number" ? marginValue : 0;
                if (desiredValue < 0) {
                    continue;
                }
                marginIndicatorElement.style[margin] = desiredValue + measurementUnit;
            }

            return marginIndicatorElement;
        };

        this.getMargin = function(direction) {
            return data['margin'][direction];
        };

        this.getMargins = function() {
            return this.getData().margin;
        };

        this.setMargin = function(template, direction, value, populating) {
            let marginIndicatorElement = this.getMarginIndicatorElement();
            const measurementUnit = template.getPaperPage().getMeasurementUnit();

            if (value < 0) {
                return;
            }
            value = parseInt(value);

            marginIndicatorElement.style[direction] = value + measurementUnit;

            if (populating) {
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
            if (!marginIndicatorElement) {
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

        this.setVisibility = function(isVisible) {
            data.visibility = isVisible;
        };

        this.get = function(field) {
            return data[field];
        };

        this.set = function(field, value, populating) {
            data[field] = value;

            if (populating) {
                return;
            }

            this.publish();
        };
    };

    let combinedPrototype = createPrototype();

    Entity.prototype = Object.create(combinedPrototype);

    Entity.prototype.toJson = function() {
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