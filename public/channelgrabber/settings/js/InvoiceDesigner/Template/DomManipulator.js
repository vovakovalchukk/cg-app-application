define(['jquery'], function($)
{
    var DomManipulator = function()
    {

    };

    DomManipulator.EVENT_TEMPLATE_CHANGED = 'invoice-template-changed';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        /*
         * TODO (CGIV-2026)
         * Use jQuery to insert the HTML in the right place
         */
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function(template)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    }

    DomManipulator.prototype.populateCustomSelect = function(id, data)
    {
        var container = $(id).parent();
        var view = {
            isOptional: false,
            id: 'domManipulated',
            name: 'foo',
            class: 'bar',
            options: []
        };

        var isFirstElement = true;
        data.forEach(function(element) {
            view['options'].push({
                title: element.getName(),
                value: element.getId(),
                selected: isFirstElement
            })
            isFirstElement = false;
        });

        require(['cg-mustache'], function(CGMustache) // TODO move into top level require?
        {
            var templateUrl = '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache';
            CGMustache.get().fetchTemplate(templateUrl, function(template, cgmustache)
            {
                var customSelect = cgmustache.renderTemplate(template, view);
                container.html(customSelect);
            });
        });
    }

    return new DomManipulator();
});