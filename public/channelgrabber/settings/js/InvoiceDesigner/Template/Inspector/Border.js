define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Border',
    'cg-mustache'
], function(
    InspectorAbstract,
    borderDomListener,
    CGMustache
) {
    var Border = function()
    {
        InspectorAbstract.call(this);

        this.setId('border');
        this.setInspectedAttributes(['borderWidth', 'borderColour']);
    };

    Border.BORDER_INSPECTOR_SELECTOR = '#border-inspector';
    Border.BORDER_INSPECTOR_BORDER_WIDTH_ID = 'border-inspector-border-width';
    Border.BORDER_INSPECTOR_BORDER_COLOUR_ID = 'border-inspector-border-colour';

    Border.prototype = Object.create(InspectorAbstract.prototype);

    Border.prototype.hide = function()
    {
        this.getDomManipulator().render(Border.BORDER_INSPECTOR_SELECTOR, "");
    };

    Border.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            border: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/border.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var borderWidth = cgmustache.renderTemplate(templates, self.getBorderWidthViewData(element), "select");
            var borderColour = cgmustache.renderTemplate(templates, self.getBorderColourViewData(element), "colourPicker");
            var border = cgmustache.renderTemplate(templates, {}, "border", {
                'borderWidth': borderWidth,
                'borderColour': borderColour
            });
            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Border',
                'id': 'border-collapsible'
            }, "collapsible", {'content': border});
            self.getDomManipulator().render(Border.BORDER_INSPECTOR_SELECTOR, collapsible);
            borderDomListener.init(self, element);
        });
    };

    Border.prototype.getBorderWidthViewData = function(element)
    {
        var BorderWidthOptions = [{
            value: '0',
            title: 'none',
            mm: '0'
        }];
        for (var borderWidthWidth = 0.5; borderWidthWidth <= 10; borderWidthWidth = borderWidthWidth + 0.5) {
            var selected = (element.getBorderWidth() == borderWidthWidth)
            BorderWidthOptions.push({'value': borderWidthWidth, 'title': borderWidthWidth + 'mm', selected: selected});
        }
        return {
            'id': Border.BORDER_INSPECTOR_BORDER_WIDTH_ID,
            'name': Border.BORDER_INSPECTOR_BORDER_WIDTH_ID,
            'options': BorderWidthOptions
        };
    };

    Border.prototype.getBorderColourViewData = function(element)
    {
        return {
            'id': Border.BORDER_INSPECTOR_BORDER_COLOUR_ID,
            'initialColour': element.getBorderColour()
        };
    };

    Border.prototype.setBorderWidth = function(element, borderWidth)
    {
        element.setBorderWidth(borderWidth);
    };

    Border.prototype.setBorderColour = function(element, borderColour)
    {
        element.setBorderColour(borderColour);
    };

    Border.prototype.getBorderInspectorBorderWidthId = function()
    {
        return Border.BORDER_INSPECTOR_BORDER_WIDTH_ID;
    };

    Border.prototype.getBorderInspectorBorderColourId = function()
    {
        return Border.BORDER_INSPECTOR_BORDER_COLOUR_ID;
    };

    return new Border();
});

