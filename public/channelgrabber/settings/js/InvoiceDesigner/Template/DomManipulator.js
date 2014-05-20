define([
    'jquery',
    'cg-mustache'
], function(
    $,
    CGMustache
) {
    var DomManipulator = function()
    {

    };

    DomManipulator.SAVE_DISCARD_BAR_SELECTOR = '#save-template';
    DomManipulator.EVENT_TEMPLATE_CHANGED = 'invoice-template-changed';
    DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED = 'invoice-template-element-selected';
    DomManipulator.EVENT_TEMPLATE_ELEMENT_RESIZED = 'invoice-template-element-resized';
    DomManipulator.EVENT_TEMPLATE_ELEMENT_MOVED = 'invoice-template-element-moved';
    DomManipulator.EVENT_IMAGE_UPLOAD_FILE_SELECTED = 'invoice-template-image-selected';
    DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER = '#invoice-template-container';
    DomManipulator.CUSTOM_SELECT_TEMPLATE_PATH = '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache';

    DomManipulator.prototype.insertTemplateHtml = function(html)
    {
        $(DomManipulator.DOM_SELECTOR_TEMPLATE_CONTAINER).empty().append(html);
    };

    DomManipulator.prototype.showSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_DISCARD_BAR_SELECTOR).show();
        return this;
    };

    DomManipulator.prototype.hideSaveDiscardBar = function(template)
    {
        $(DomManipulator.SAVE_DISCARD_BAR_SELECTOR).hide();
        return this;
    };

    DomManipulator.prototype.triggerTemplateChangeEvent = function (template)
    {
        this.showSaveDiscardBar();
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_CHANGED, [template]);
        return this;
    };

    DomManipulator.prototype.populateCustomSelect = function(selector, data)
    {
        var container = $(selector).parent().parent();

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

        CGMustache.get().fetchTemplate(DomManipulator.CUSTOM_SELECT_TEMPLATE_PATH, function(template, cgmustache) {
            container.empty().html(cgmustache.renderTemplate(template, view));
        });
    };

    DomManipulator.prototype.show = function(selector)
    {
        $(selector).removeClass('hidden');
    }

    DomManipulator.prototype.triggerElementSelectedEvent = function(element)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED, [element]);
        return this;
    };

    DomManipulator.prototype.getTemplateChangedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_CHANGED;
    };

    DomManipulator.prototype.enable = function(selector)
    {
        $(selector).removeClass('disabled');
    };

    DomManipulator.prototype.show = function(selector)
    {
        $(selector).show();
    };

    DomManipulator.prototype.reloadName = function(selector, template)
    {
        $(selector).val(template.getName());
    };

    DomManipulator.prototype.getElementSelectedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED;
    };

    DomManipulator.prototype.getElementResizedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_ELEMENT_RESIZED;
    };

    DomManipulator.prototype.getElementMovedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_ELEMENT_MOVED;
    };

    DomManipulator.prototype.getImageUploadFileSelectedEvent = function()
    {
        return DomManipulator.EVENT_IMAGE_UPLOAD_FILE_SELECTED;
    };

    return new DomManipulator();
});
