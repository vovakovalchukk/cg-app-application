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
            align: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            font: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/font.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var fontSizeOptions = [];
            for (var fontSizeSize = 6; fontSizeSize <= 72; fontSizeSize++) {
                var selected = false;
                if (element.getFontSize() == fontSizeSize) {
                    selected = true;
                }
                fontSizeOptions.push({'value': fontSizeSize, 'title': fontSizeSize + 'pt', selected: selected});
            }
            var fontSize = cgmustache.renderTemplate(templates, {
                'name': Font.FONT_INSPECTOR_FONT_SIZE_ID,
                'options': fontSizeOptions
            }, "select");
            var fontFamilyOptions = [
                {'title': 'Courier New', 'value': 'Courier'},
                {'title': 'Helvetica'},
                {'title': 'Times New Roman', 'value': 'Times'}
            ];
            var fontFamily = cgmustache.renderTemplate(templates, {
                'name': Font.FONT_INSPECTOR_FONT_FAMILY_ID,
                'options': fontFamilyOptions
            }, "select");
            var fontColour = cgmustache.renderTemplate(templates, {'id': Font.FONT_INSPECTOR_FONT_COLOUR_ID}, "colourPicker");
            var align = cgmustache.renderTemplate(templates, {'id': Font.FONT_INSPECTOR_ALIGN_ID}, "align");
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

