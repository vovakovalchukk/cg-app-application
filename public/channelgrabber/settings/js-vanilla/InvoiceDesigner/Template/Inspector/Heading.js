define([
    'InvoiceDesigner/Template/InspectorAbstract',
    'InvoiceDesigner/Template/Inspector/DomListener/Heading',
    'cg-mustache'
], function(
    InspectorAbstract,
    headingDomListener,
    CGMustache
) {
    var Heading = function()
    {
        InspectorAbstract.call(this);

        this.setId('heading');
        this.setInspectedAttributes([]);
    };

    Heading.HEADING_INSPECTOR_SELECTOR = '#heading-inspector';
    Heading.HEADING_INSPECTOR_DELETE_ID = 'heading-delete-button';

    Heading.prototype = Object.create(InspectorAbstract.prototype);

    Heading.prototype.hide = function()
    {
        this.getDomManipulator().render(Heading.HEADING_INSPECTOR_SELECTOR, "");
    };

    Heading.prototype.showForElement = function(element, template, service)
    {
        var self = this;
        var templateUrlMap = {
            button: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            heading: '/channelgrabber/settings/template/InvoiceDesigner/Template/Inspector/heading.mustache'
        };
        CGMustache.get().fetchTemplates(templateUrlMap, function(templates, cgmustache)
        {
            var button = cgmustache.renderTemplate(templates, {'buttons' : true, 'value' : 'Delete', 'id' : Heading.HEADING_INSPECTOR_DELETE_ID}, "button");
            var heading = cgmustache.renderTemplate(templates, {'type' : element.getType()}, "heading", {'button': button});
            self.getDomManipulator().render(Heading.HEADING_INSPECTOR_SELECTOR, heading);
            headingDomListener.init(self, template, element, service);
        });
    };

    Heading.prototype.removeElement = function(template, element)
    {
        template.removeElement(element);
    };

    Heading.prototype.getHeadingInspectorDeleteId = function()
    {
        return Heading.HEADING_INSPECTOR_DELETE_ID;
    };

    Heading.prototype.getHeadingInspectorSelector = function() {
        return Heading.HEADING_INSPECTOR_SELECTOR;
    };

    return new Heading();
});