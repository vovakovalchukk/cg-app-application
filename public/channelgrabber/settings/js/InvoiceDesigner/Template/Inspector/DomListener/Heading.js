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
        console.log(inspector);
        $('#' + inspector.getHeadingInspectorDeleteId()).off('click').on('click', function() {
            inspector.removeElement(template, element, service);
        });
    };

    return new Heading();
});