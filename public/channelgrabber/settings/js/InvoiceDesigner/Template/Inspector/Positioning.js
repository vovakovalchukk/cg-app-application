define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Positioning',
    'cg-mustache'
], function(
    InspectorAbstract,
    positioningDomListener,
    CGMustache
) {
    var Positioning = function()
    {
        InspectorAbstract.call(this);

        this.setId('positioning');
        this.setInspectedAttributes(['height', 'width', 'x', 'y']);
    };

    Positioning.POSITIONING_INSPECTOR_SELECTOR = '#positioning-inspector';
    Positioning.POSITIONING_INSPECTOR_LEFT_ID = 'positioning-inspector-left';
    Positioning.POSITIONING_INSPECTOR_TOP_ID = 'positioning-inspector-top';
    Positioning.POSITIONING_INSPECTOR_WIDTH_ID = 'positioning-inspector-wifth';
    Positioning.POSITIONING_INSPECTOR_HEIGHT_ID = 'positioning-inspector-height';

    Positioning.prototype = Object.create(InspectorAbstract.prototype);

    Positioning.prototype.hide = function()
    {
        this.getDomManipulator().render(Positioning.POSITIONING_INSPECTOR_SELECTOR, "");
    };

    Positioning.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            text: '/channelgrabber/zf2-v4-ui/templates/elements/text.mustache',
            positioning: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/positioning.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var left = cgmustache.renderTemplate(templates, {'id' : Positioning.POSITIONING_INSPECTOR_LEFT_ID, 'value' : element.getX()}, "text");
            var top = cgmustache.renderTemplate(templates, {'id' : Positioning.POSITIONING_INSPECTOR_TOP_ID, 'value' : element.getY()}, "text");
            var width = cgmustache.renderTemplate(templates, {'id' : Positioning.POSITIONING_INSPECTOR_WIDTH_ID, 'value' : element.getWidth()}, "text");
            var height = cgmustache.renderTemplate(templates, {'id' : Positioning.POSITIONING_INSPECTOR_HEIGHT_ID, 'value' : element.getHeight()}, "text");
            var positioning = cgmustache.renderTemplate(templates, {}, "positioning", {
                'left': left,
                'top': top,
                'width': width,
                'height': height
            });
            var collapsible = cgmustache.renderTemplate(templates, {
                'display' : true,
                'title': 'Location & Size',
                'id': 'positioning-collapsible'
            }, "collapsible", {'content': positioning});
            self.getDomManipulator().render(Positioning.POSITIONING_INSPECTOR_SELECTOR, collapsible);
            positioningDomListener.init(self, element);
        });
    };

    Positioning.prototype.updatePosition = function(position)
    {
        this.getDomManipulator().setValue('#'+Positioning.POSITIONING_INSPECTOR_LEFT_ID, position.left.pxToMm().roundToNearest(0.5));
        this.getDomManipulator().setValue('#'+Positioning.POSITIONING_INSPECTOR_TOP_ID, position.top.pxToMm().roundToNearest(0.5));
    };

    Positioning.prototype.updateSize = function(size)
    {
        this.getDomManipulator().setValue('#'+Positioning.POSITIONING_INSPECTOR_WIDTH_ID, size.width.pxToMm().roundToNearest(0.5));
        this.getDomManipulator().setValue('#'+Positioning.POSITIONING_INSPECTOR_HEIGHT_ID, size.height.pxToMm().roundToNearest(0.5));
    };

    Positioning.prototype.getPositioningInspectorLeftId = function()
    {
        return Positioning.POSITIONING_INSPECTOR_LEFT_ID;
    };

    Positioning.prototype.getPositioningInspectorTopId = function()
    {
        return Positioning.POSITIONING_INSPECTOR_TOP_ID;
    };

    Positioning.prototype.getPositioningInspectorWidthId = function()
    {
        return Positioning.POSITIONING_INSPECTOR_WIDTH_ID;
    };

    Positioning.prototype.getPositioningInspectorHeightId = function()
    {
        return Positioning.POSITIONING_INSPECTOR_HEIGHT_ID;
    };

    return new Positioning();
});