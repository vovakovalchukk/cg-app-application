define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {

    var Positioning = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Positioning.EVENTS = 'keyup paste';

    Positioning.prototype.init = function(inspector, element)
    {
        var timeoutId;
        var timeout = 700;

        var that = this;
        $('#' + inspector.getPositioningInspectorLeftId() + ',' +
            '#' + inspector.getPositioningInspectorTopId() + ',' +
            '#' + inspector.getPositioningInspectorHeightId()  + ',' +
            '#' + inspector.getPositioningInspectorWidthId()).off(Positioning.EVENTS).on(Positioning.EVENTS, function(event) {

            var selector = this;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function() {
                that.set(selector, inspector, element);
            }, timeout);
        });
    };

    Positioning.prototype.set = function(selector, inspector, element)
    {
        var that = this;
        var value = this.getDomManipulator().getValue(selector);

        if (value.slice(-1) == '.') {
            value = parseFloat(value) + 0.5;
        } else {
            value = parseFloat(value);
        }

        if (isNaN(value)) {
            value = 0;
        }

        this.getDomManipulator().setValue(selector, value.roundToNearest(0.5));
        element.setX(that.getDomManipulator().getValue('#' + inspector.getPositioningInspectorLeftId()))
                .setY(that.getDomManipulator().getValue('#' + inspector.getPositioningInspectorTopId()))
                .setWidth(that.getDomManipulator().getValue('#' + inspector.getPositioningInspectorWidthId()))
                .setHeight(that.getDomManipulator().getValue('#' + inspector.getPositioningInspectorHeightId()));
    };

    return new Positioning();
});