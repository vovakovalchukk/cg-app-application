define([
    'jquery'
], function(
    $
) {

    var Heading = function()
    {
    };

    Heading.prototype.init = function(inspector, template, element, service)
    {
        $('#' + inspector.getHeadingInspectorDeleteId()).off('click').on('click', function() {
            inspector.removeElement(template, element);
        });
    };

    return new Heading();
});