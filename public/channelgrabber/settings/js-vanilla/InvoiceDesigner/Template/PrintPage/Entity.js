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
            value = parseInt(value);
            state.margin[direction] = value;
            marginIndicatorElement.style[direction] = value + state.measurement;
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
            return dimension[dimension]
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