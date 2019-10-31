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
            var fontSize = cgmustache.renderTemplate(templates, self.getFontSizeViewData(element.getFontSize()), "select");
            var fontFamily = cgmustache.renderTemplate(templates, self.getFontFamilyViewData(element.getFontFamily()), "select");
            var fontColour = cgmustache.renderTemplate(templates, self.getFontColourViewData( element.getFontColour()), "colourPicker");
            var font = cgmustache.renderTemplate(templates, {}, "font", {
                'fontSize': fontSize,
                'fontFamily': fontFamily,
                'fontColour': fontColour
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

    Font.prototype.getFontSizeViewData = function(fontSizeSelected, id)
    {
        var fontSizeOptions = [];
        for (var fontSizeSize = Font.MINIMUM_FONT_SIZE; fontSizeSize <= Font.MAXIMUM_FONT_SIZE; fontSizeSize++) {
            var selected = (fontSizeSelected == fontSizeSize);
            fontSizeOptions.push({'value': fontSizeSize, 'title': fontSizeSize + 'pt', selected: selected});
        }
        return {
            'id': id || Font.FONT_INSPECTOR_FONT_SIZE_ID,
            'name': Font.FONT_INSPECTOR_FONT_SIZE_ID,
            'options': fontSizeOptions
        };
    };

    Font.prototype.getFontFamilyViewData = function(fontFamilySelected, id)
    {
        var fontFamilyOptions = [
            {'title': 'Courier New', 'value': 'Courier'},
            {'title': 'Arial', 'value': 'Arial'},
            {'title': 'Times New Roman', 'value': 'TimesRoman'}
        ];
        for (var key in fontFamilyOptions) {
            if (fontFamilyOptions[key]['value'] == fontFamilySelected) {
                fontFamilyOptions[key]['selected'] = true;
            }
        }
        return {
            'id': id || Font.FONT_INSPECTOR_FONT_FAMILY_ID,
            'name': Font.FONT_INSPECTOR_FONT_FAMILY_ID,
            'options': fontFamilyOptions
        };
    };

    Font.prototype.getFontColourViewData = function(fontColorSelected, id)
    {
        return {
            'id': id || Font.FONT_INSPECTOR_FONT_COLOUR_ID,
            'initialColour': fontColorSelected
        };
    };

    Font.prototype.getFontAlignViewData = function(alignSelected, id)
    {
        var alignViewData = {
            'id': id || Font.FONT_INSPECTOR_ALIGN_ID,
            'containerClass': 'u-display-flex'
        };
        alignViewData[alignSelected] = true;
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

