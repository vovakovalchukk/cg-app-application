define([
    'InvoiceDesigner/Template/Service',
    'InvoiceDesigner/EntityHydrateAbstract',
    'InvoiceDesigner/PubSubAbstract',
], function(
    templateService,
    EntityHydrateAbstract,
    PubSubAbstract
) {
    let Entity = function() {
        EntityHydrateAbstract.call(this);
        PubSubAbstract.call(this);

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

        let marginIndicatorElement = null;
        let visibility = false;

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