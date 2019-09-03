define([
    'jquery',
    'InvoiceDesigner/Template/DomManipulator'
], function(
    $,
    domManipulator
) {

    var Text = function()
    {
        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    Text.prototype.init = function(inspector, element)
    {
        var timeoutId;
        var timeout = 1000;
        var self = this;

        $('#' + inspector.getTextInspectorTextId()).off('change').on('change', function () {
            var textarea = this;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function() {
                self.styleText(textarea, inspector, element)
                    .initDataFieldsChangeListener(inspector, element);
            }, timeout);
        });

        self.initRemoveEmptyLinesToggleListener(inspector, element);
        return this;
    };

    Text.prototype.initDataFieldsChangeListener = function(inspector, element)
    {
        $('#' + inspector.getTextInspectorDataFieldsId()).off('change').on('change', function(event, container, value) {
            inspector.dataFieldSelected(container, value);
        });
        return this;
    };

    Text.prototype.initRemoveEmptyLinesToggleListener = function(inspector, element)
    {
        $('#' + inspector.getRemoveBlankLinesId()).off('change').on('change', function(event, container, value) {
            var isSelected = $('#' + inspector.getRemoveBlankLinesId()).is(":checked");
            element.setRemoveBlankLines(isSelected);
        });
        return this;
    };

    Text.prototype.styleText = function(textarea, inspector, element)
    {
        var text = $(textarea).val().replace(/<br \/>/gi, '\n')
            .replace(/<p>|<\/p>/gi, '')
            .replace(/&nbsp;/gi, ' ')
            .replace(/<strong><em>|<em><strong>/gi, '%%bi%%')
            .replace(/<strong>/gi, '%%b%%')
            .replace(/<em>/gi, '%%i%%')
            .replace(/<\/em>|<\/strong>/gi, '%%n%%')
            .replace(/%%n%%%%n%%/gi, '%%n%%');
        inspector.setText(element, text);
        return this;
    };

    return new Text();
});