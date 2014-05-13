define(['jquery', 'cg-mustache'], function($, CGMustache)
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

    DomManipulator.prototype.populateCustomSelect = function(selector, data)
    {
        var container = $(selector).parent();
        var view = {
            isOptional: $(selector).hasClass("filter-optional"),
            id: $(selector).attr('id'),
            name: $(selector + " input:first").attr('name'),
            class: $(selector + " input:first").attr('class'),
            options: []
        };

        var isFirstElement = true;
        data.forEach(function(element) {
            view['options'].push({
                title: element.getName(),
                value: element.getId(),
                selected: isFirstElement
            });
            isFirstElement = false;
        });

        var templateUrl = '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache';
        CGMustache.get().fetchTemplate(templateUrl, function(template, cgmustache) {
            container.html(cgmustache.renderTemplate(template, view));
        });
    }

    return new DomManipulator();
});