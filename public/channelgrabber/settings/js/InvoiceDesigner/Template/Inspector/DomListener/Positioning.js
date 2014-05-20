define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
    ) {

    var Positioning = function()
    {
        var timeout;

        this.getTimeout = function()
        {
            return timeout;
        };

        this.setTimeout = function(newTimeout)
        {
            timeout = newTimeout;
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Positioning.EVENTS = 'keyup paste';

    Positioning.prototype.init = function(inspector, element)
    {
        var that = this;
        $('#' + inspector.getPositioningInspectorLeftId() + ',' +
            '#' + inspector.getPositioningInspectorTopId() + ',' +
            '#' + inspector.getPositioningInspectorHeightId()  + ',' +
            '#' + inspector.getPositioningInspectorWidthId()).off(Positioning.EVENTS).on(Positioning.EVENTS, function(event) {
            that.set(event, this, inspector, element);
        });
    };

    Positioning.prototype.set = function(event, selector, inspector, element)
    {
        var value = Number($(selector).val());
        if (event.key == '.') {
            value += + 0.5;
        }

        if (isNaN(value)) {
            return;
        }

        this.getDomManipulator().setValue(selector, value.roundToNearest(0.5));
        clearTimeout(this.getTimeout());
        this.setTimeout(setTimeout(function() {
            element.setX($('#' + inspector.getPositioningInspectorLeftId()).val())
                .setY($('#' + inspector.getPositioningInspectorTopId()).val())
                .setWidth($('#' + inspector.getPositioningInspectorWidthId()).val())
                .setHeight($('#' + inspector.getPositioningInspectorHeightId()).val());
        }, 500));
    };

    return new Positioning();
});