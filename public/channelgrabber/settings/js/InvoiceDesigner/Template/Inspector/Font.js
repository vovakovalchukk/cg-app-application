define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Font',
    'cg-mustache'
], function(
    InspectorAbstract,
    fontDomListener,
    CGMustache
) {
    var Font = function()
    {
        InspectorAbstract.call(this);

        this.setId('font');
        this.setInspectedAttributes(['fontSize', 'fontFamily', 'fontColour', 'align']);
    };

    Font.FONT_INSPECTOR_SELECTOR = '#font-inspector';
    Font.FONT_INSPECTOR_FONT_SIZE_ID = 'font-inspector-font-size';
    Font.FONT_INSPECTOR_FONT_FAMILY_ID = 'font-inspector-font-family';
    Font.FONT_INSPECTOR_FONT_COLOUR_ID = 'font-inspector-font-colour';
    Font.FONT_INSPECTOR_ALIGN_ID = 'font-inspector-align';
    Font.MINIMUM_FONT_SIZE = 6;
    Font.MAXIMUM_FONT_SIZE = 72;

    Font.prototype = Object.create(InspectorAbstract.prototype);

    Font.prototype.hide = function()
    {
        this.getDomManipulator().render(Font.FONT_INSPECTOR_SELECTOR, "");
    };

    Font.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            select: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            colourPicker: '/channelgrabber/zf2-v4-ui/templates/elements/colour-picker.mustache',
            align: '/channelgrabber/zf2-v4-ui/templates/elements/align.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var fontSize = cgmustache.renderTemplate(templates, self.getFontSizeViewData(element), "select");
            var fontFamily = cgmustache.renderTemplate(templates, self.getFontFamilyViewData(element), "select");
            var fontColour = cgmustache.renderTemplate(templates, self.getFontColourViewData(element), "colourPicker");
            var align = cgmustache.renderTemplate(templates, self.getFontAlignViewData(element), "align");
            var font = cgmustache.renderTemplate(templates, {}, "font", {
                'fontSize': fontSize,
                'fontFamily': fontFamily,
                'fontColour': fontColour,
                'align': align
            });
            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Font',
                'id': 'font-collapsible'
            }, "collapsible", {'content': font});
            self.getDomManipulator().render(Font.FONT_INSPECTOR_SELECTOR, collapsible);
            fontDomListener.init(self, element);
        });
    };

    Font.prototype.getFontSizeViewData = function(element)
    {
        var fontSizeOptions = [];
        for (var fontSizeSize = Font.MINIMUM_FONT_SIZE; fontSizeSize <= Font.MAXIMUM_FONT_SIZE; fontSizeSize++) {
            var selected = (element.getFontSize() == fontSizeSize);
            fontSizeOptions.push({'value': fontSizeSize, 'title': fontSizeSize + 'pt', selected: selected});
        }
        return {
            'id': Font.FONT_INSPECTOR_FONT_SIZE_ID,
            'name': Font.FONT_INSPECTOR_FONT_SIZE_ID,
            'options': fontSizeOptions
        };
    };

    Font.prototype.getFontFamilyViewData = function(element)
    {
        var fontFamilyOptions = [
            {'title': 'Courier New', 'value': 'Courier'},
            {'title': 'Helvetica', 'value': 'Helvetica'},
            {'title': 'Times New Roman', 'value': 'Times'}
        ];
        for (var key in fontFamilyOptions) {
            if (fontFamilyOptions[key]['value'] == element.getFontFamily()) {
                fontFamilyOptions[key]['selected'] = true;
            }
        }
        return {
            'id': Font.FONT_INSPECTOR_FONT_FAMILY_ID,
            'name': Font.FONT_INSPECTOR_FONT_FAMILY_ID,
            'options': fontFamilyOptions
        };
    };

    Font.prototype.getFontColourViewData = function(element)
    {
        return {
            'id': Font.FONT_INSPECTOR_FONT_COLOUR_ID,
            'initialColour': element.getFontColour()
        };
    };

    Font.prototype.getFontAlignViewData = function(element)
    {
        var alignViewData = {
            'id': Font.FONT_INSPECTOR_ALIGN_ID
        };
        alignViewData[element.getAlign()] = true;
        return alignViewData;
    };

    Font.prototype.setFontFamily = function(element, fontFamily)
    {
        element.setFontFamily(fontFamily);
    };

    Font.prototype.setFontSize = function(element, fontSize)
    {
        element.setFontSize(fontSize);
    };

    Font.prototype.setAlign = function(element, align)
    {
        element.setAlign(align);
    };

    Font.prototype.setFontColour = function(element, colour)
    {
        element.setFontColour(colour);
    };

    Font.prototype.getFontInspectorFontSizeId = function()
    {
        return Font.FONT_INSPECTOR_FONT_SIZE_ID;
    };

    Font.prototype.getFontInspectorFontFamilyId = function()
    {
        return Font.FONT_INSPECTOR_FONT_FAMILY_ID;
    };

    Font.prototype.getFontInspectorFontColourId = function()
    {
        return Font.FONT_INSPECTOR_FONT_COLOUR_ID;
    };

    Font.prototype.getFontInspectorAlignId = function()
    {
        return Font.FONT_INSPECTOR_ALIGN_ID;
    };

    return new Font();
});
