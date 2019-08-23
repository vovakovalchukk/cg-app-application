define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityAbstract',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSub/Abstract',
    'InvoiceDesigner/Constants',
    'InvoiceDesigner/utility'
], function(
    templateService,
    EntityAbstract,
    EntityHydrateAbstract,
    PubSubAbstract,
    Constants,
    utility
) {
    const Entity = function() {
        EntityHydrateAbstract.call(this);
        EntityAbstract.call(this);
        PubSubAbstract.call(this);

        let data = {
            margin: {
                top: null,
                bottom: null,
                left: null,
                right: null
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

        this.getEntityName = function() {
            return 'PrintPage';
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
            marginIndicatorElement.style.height = this.getHeight(template) + measurementUnit;
            marginIndicatorElement.style.width = this.getWidth(template) + measurementUnit;

            if (!data.margin) {
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

            this.set("margin", newMarginState, false, [{
                topicName: this.getTopicNames().paperSpace,
                template,
                dimensionAffected: Constants.MARGIN_TO_DIMENSION[direction],
                populating: false
            }]);
        };

        this.getHeight = function(template) {
            const paperPage = template.getPaperPage();

            let top = data.margin['top'] || 0;
            let bottom = data.margin['bottom'] || 0;

            let height = paperPage.getHeight() - top - bottom;
            return height;
        };

        this.getWidth = function(template) {
            const paperPage = template.getPaperPage();

            let left = data.margin['left'] || 0;
            let right = data.margin['right'] || 0;

            let width = paperPage.getWidth() - left - right;
            return width;
        };

        this.setVisibility = function(isVisible) {
            data.visibility = isVisible;
        };
    };

    Entity.prototype = Object.create(utility.createPrototype([
        EntityHydrateAbstract,
        PubSubAbstract,
        EntityAbstract
    ]));

    Entity.prototype.toJson = function() {
        let data = Object.assign({}, this.getData());
        delete data.visibility;
        delete data.dimension;
        let json = JSON.parse(JSON.stringify(data));
        return json;
    };

    return Entity;
});