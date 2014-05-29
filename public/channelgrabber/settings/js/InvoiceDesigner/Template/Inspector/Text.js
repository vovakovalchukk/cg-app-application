define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Text',
    'cg-mustache',
    'tinyMCE'
], function(
    InspectorAbstract,
    textDomListener,
    CGMustache,
    tinyMCE
) {
    var Text = function()
    {
        InspectorAbstract.call(this);

        this.setId('text');
        this.setInspectedAttributes(['text']);
    };

    Text.TEXT_INSPECTOR_SELECTOR = '#text-inspector';
    Text.TEXT_INSPECTOR_TEXT_ID = 'text-inspector-text';

    Text.prototype = Object.create(InspectorAbstract.prototype);

    Text.prototype.hide = function()
    {
        tinyMCE.execCommand('mceRemoveEditor', false, Text.TEXT_INSPECTOR_TEXT_ID);
        this.getDomManipulator().render(Text.TEXT_INSPECTOR_SELECTOR, "");
    };

    Text.prototype.showForElement = function(element)
    {
        var self = this;
        var templateUrlMap = {
            textarea: '/channelgrabber/zf2-v4-ui/templates/elements/textarea/bold-italic.mustache',
            text: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/text.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var textarea = cgmustache.renderTemplate(templates, self.getTextViewData(element), "textarea");
            var text = cgmustache.renderTemplate(templates, {}, "text", {'textarea': textarea});
            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Text',
                'id': 'text-collapsible'
            }, "collapsible", {'content': text});
            self.getDomManipulator().render(Text.TEXT_INSPECTOR_SELECTOR, collapsible);
            textDomListener.init(self, element);
        });
    };

    Text.prototype.getTextViewData = function(element)
    {
        var text = (element.getText().replace(/%%(b|bi|i|n|ib)%%/gi, '</><$1>') + '</>')
            .replace(/<b>([\s\S]*?)<\/>/gi, '<strong>$1</strong>')
            .replace(/<i>([\s\S]*?)<\/>/gi, '<em>$1</em>')
            .replace(/<ib>|<bi>([\s\S]*?)<\/>/gi, '<strong><em>$1</em></strong>')
            .replace(/<n>|<\/>/gi, '');
        return {
            'id': Text.TEXT_INSPECTOR_TEXT_ID,
            'content': text
        };
    };

    Text.prototype.setText = function(element, text)
    {
        element.setText(text);
    };

    Text.prototype.getTextInspectorTextId = function()
    {
        return Text.TEXT_INSPECTOR_TEXT_ID;
    };

    return new Text();
});


