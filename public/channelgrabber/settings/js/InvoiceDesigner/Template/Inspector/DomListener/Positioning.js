define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator',
    'element/ElementCollection'
], function(
    $,
    domManipulator,
    elementCollection
) {

    var Positioning = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Positioning.EVENTS = 'keyup paste';
    Positioning.DROPDOWN_EVENTS = 'change';

    Positioning.prototype.init = function(inspector, element)
    {
        this.setupInputFieldsHandler(inspector, element);
        this.setupSizeDropdownHandler(inspector, element);
        this.initResizeMoveListeners(inspector);
    };

    Positioning.prototype.setupInputFieldsHandler = function(inspector, element)
    {
        var timeoutId;
        var timeout = 700;

        var self = this;
        $('#' + inspector.getPositioningInspectorLeftId() + ',' +
        '#' + inspector.getPositioningInspectorTopId() + ',' +
        '#' + inspector.getPositioningInspectorHeightId() + ',' +
        '#' + inspector.getPositioningInspectorWidthId()).off(Positioning.EVENTS).on(Positioning.EVENTS, function (event) {

            var selector = this;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function () {
                self.set(selector, inspector, element);
            }, timeout);
        });
    };

    Positioning.prototype.initResizeMoveListeners = function(inspector)
    {
        $(document).on(this.getDomManipulator().getElementMovedEvent(), function(event, elementDomId, position)
        {
            inspector.updatePosition(position);
        });
        $(document).on(this.getDomManipulator().getElementResizedEvent(), function(event, elementDomId, position, size) {
            inspector.updateSize(size);
            inspector.updatePosition(position);
        });
    };

    Positioning.prototype.setupSizeDropdownHandler = function(inspector, element)
    {
        var self = this;
        $('#' + inspector.getPositioningInspectorSizeId()).off(Positioning.DROPDOWN_EVENTS).on(Positioning.DROPDOWN_EVENTS, function(event) {
            var customSelect = elementCollection.get(inspector.getPositioningInspectorSizeId());

            var option = customSelect.getValue();
            var dimensions = element.getDimensionsForSizeOption(option);
            element.setSizeOption(option);

            $('#' + inspector.getPositioningInspectorWidthId()).val(dimensions.width);
            $('#' + inspector.getPositioningInspectorHeightId()).val(dimensions.height);

            self.set('#' + inspector.getPositioningInspectorWidthId(), inspector, element);
            self.set('#' + inspector.getPositioningInspectorHeightId(), inspector, element);
        });
    };

    Positioning.prototype.set = function(selector, inspector, element)
    {
        var self = this;
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
        element.setX(self.getDomManipulator().getValue('#' + inspector.getPositioningInspectorLeftId()))
                .setY(self.getDomManipulator().getValue('#' + inspector.getPositioningInspectorTopId()))
                .setWidth(self.getDomManipulator().getValue('#' + inspector.getPositioningInspectorWidthId()))
                .setHeight(self.getDomManipulator().getValue('#' + inspector.getPositioningInspectorHeightId()));
    };

    return new Positioning();
});