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
        this.initTextChangeListener(inspector, element)
            .initDataFieldsChangeListener(inspector, element);
    };

    Text.prototype.initTextChangeListener = function(inspector, element)
    {
        $('#' + inspector.getTextInspectorTextId()).off('change').on('change', function()
        {
            var text = $(this).val().replace(/<br \/>/gi, '\n')
                .replace(/<p>|<\/p>/gi, '')
                .replace(/<strong><em>|<em><strong>/gi, '%%bi%%')
                .replace(/<strong>/gi, '%%b%%')
                .replace(/<em>/gi, '%%i%%')
                .replace(/<\/em>|<\/strong>/gi, '%%n%%')
                .replace(/%%n%%%%n%%/gi, '%%n%%');
            inspector.setText(element, text);
        });
        return this;
    };

    Text.prototype.initDataFieldsChangeListener = function(inspector, element)
    {
        $('#' + inspector.getTextInspectorDataFieldsId()).off('change').on('change', function(event, container, value)
        {
            inspector.dataFieldSelected(container, value);
        });
    };

    return new Text();
});