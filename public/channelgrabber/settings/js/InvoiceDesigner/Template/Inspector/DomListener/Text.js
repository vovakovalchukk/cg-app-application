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
        var self = this;
        $('#' + inspector.getTextInspectorTextId()).off('change').on('change', function() {
            var text = $(this).val().replace(/<br \/>/gi, '\n')
                .replace(/<p>|<\/p>/gi, '')
                .replace(/<strong><em>|<em><strong>/gi, '%%bi%%')
                .replace(/<strong>/gi, '%%b%%')
                .replace(/<em>/gi, '%%i%%')
                .replace(/<\/em>|<\/strong>/gi, '%%n%%');

            console.log(text);
            inspector.setText(element, text);
        });
    };

    return new Text();
});