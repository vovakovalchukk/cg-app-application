define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Text',
    'cg-mustache',
    'tinyMCE',
    'element/customSelect'
], function(
    InspectorAbstract,
    textDomListener,
    CGMustache,
    tinyMCE,
    CustomSelect
) {
    var Text = function()
    {
        InspectorAbstract.call(this);

        this.setId('text');
        this.setInspectedAttributes(['text']);

        var dataFieldOptions = [];

        this.getDataFieldOptions = function()
        {
            return dataFieldOptions;
        };

        this.setDataFieldOptions = function(newOptions)
        {
            dataFieldOptions = newOptions;
            return this;
        };
    };

    Text.TEXT_INSPECTOR_SELECTOR = '#text-inspector';
    Text.TEXT_INSPECTOR_TEXT_ID = 'text-inspector-text';
    Text.TEXT_INSPECTOR_DATA_FIELDS_ID = 'text-inspector-data-fields';
    Text.TEXT_INSPECTOR_REMOVE_BLANK_LINES_ID = 'removeBlankLinesCheckbox';

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
            dataFields: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache',
            text: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/text.mustache',
            removeBlankLines: '/channelgrabber/zf2-v4-ui/templates/elements/checkbox.mustache',
            collapsible: '/channelgrabber/zf2-v4-ui/templates/elements/collapsible.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            const textarea = cgmustache.renderTemplate(templates, self.getTextViewData(element), "textarea");
            const dataFields = cgmustache.renderTemplate(templates, self.getDataFieldsData(), "dataFields");
            const removeBlankLines = cgmustache.renderTemplate(templates, self.getRemoveBlankLinesData(element), "removeBlankLines");

            var text = cgmustache.renderTemplate(templates, {}, "text", {
                textarea,
                dataFields,
                removeBlankLines,
            });

            var collapsible = cgmustache.renderTemplate(templates, {
                'display': true,
                'title': 'Text',
                'id': 'text-collapsible'
            }, "collapsible", {'content': text});
            self.getDomManipulator().render(Text.TEXT_INSPECTOR_SELECTOR, collapsible);
            textDomListener.init(self, element);
        });
    };

    Text.prototype.getRemoveBlankLinesData = function(element)
    {
        return {
            class: 'remove-blank-lines-checkbox',
            selected: element.getRemoveBlankLines(),
            id: this.getRemoveBlankLinesId(),
            name: 'removeBlankLines',
            label: 'Remove Blank Lines'
        };
    };

    Text.prototype.getTextViewData = function(element)
    {
        var text = (element.getText().replace(/%%(b|bi|i|n)%%/gi, '</><$1>') + '</>')
            .replace(/<b>([\s\S]*?)<\/>/gi, '<strong>$1</strong>')
            .replace(/<i>([\s\S]*?)<\/>/gi, '<em>$1</em>')
            .replace(/<bi>([\s\S]*?)<\/>/gi, '<strong><em>$1</em></strong>')
            .replace(/<n>|<\/>/gi, '');
        return {
            'id': Text.TEXT_INSPECTOR_TEXT_ID,
            'content': text
        };
    };

    Text.prototype.getDataFieldsData = function()
    {
        var options = [];
        this.getDataFieldOptions().forEach(function(option)
        {
            options.push({title: option});
        });
        return {
            initialTitle: 'Select Data Field',
            id: Text.TEXT_INSPECTOR_DATA_FIELDS_ID,
            name: Text.TEXT_INSPECTOR_DATA_FIELDS_ID,
            options: options
        };
    }

    Text.prototype.setText = function(element, text)
    {
        element.setText(text);
    };

    Text.prototype.dataFieldSelected = function(selectElement, dataField)
    {
        tinyMCE.get(Text.TEXT_INSPECTOR_TEXT_ID).selection.setContent(dataField);
    };

    Text.prototype.getTextInspectorTextId = function()
    {
        return Text.TEXT_INSPECTOR_TEXT_ID;
    };

    Text.prototype.getTextInspectorDataFieldsId = function()
    {
        return Text.TEXT_INSPECTOR_DATA_FIELDS_ID;
    };

    Text.prototype.getRemoveBlankLinesId = function()
    {
        return Text.TEXT_INSPECTOR_REMOVE_BLANK_LINES_ID;
    };

    return new Text();
});


