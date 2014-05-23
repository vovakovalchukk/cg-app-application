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

    DomManipulator.prototype.triggerElementSelectedEvent = function(element)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_ELEMENT_SELECTED, [element]);
        return this;
    };

    DomManipulator.prototype.triggerElementResizedEvent = function(elementId, position, size)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_ELEMENT_RESIZED, [elementId, position, size]);
        return this;
    };

    DomManipulator.prototype.triggerElementMovedEvent = function(elementId, position)
    {
        $(document).trigger(DomManipulator.EVENT_TEMPLATE_ELEMENT_MOVED, [elementId, position]);
        return this;
    };

    DomManipulator.prototype.populateCustomSelect = function(selector, data, selectedValue)
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
                selected: ((!selectedValue && isFirstElement) || element.getId() === selectedValue)
            });
            isFirstElement = false;
        });

        CGMustache.get().fetchTemplate(DomManipulator.CUSTOM_SELECT_TEMPLATE_PATH, function(template, cgmustache) {
            container.empty().html(cgmustache.renderTemplate(template, view));
        });
    };

    DomManipulator.prototype.enable = function(selector)
    {
        $(selector).removeClass('disabled');
    };

    DomManipulator.prototype.show = function(selector)
    {
        $(selector).show();
    };

    DomManipulator.prototype.markAsActive = function(selector)
    {
        $(selector).addClass('active');
    };

    DomManipulator.prototype.markAsInactive = function(selector)
    {
        $(selector).removeClass('active');
    };

    DomManipulator.prototype.reloadName = function(selector, template)
    {
        $(selector).val(template.getName());
    };

    DomManipulator.prototype.getOffset = function(selector)
    {
        return $(selector).offset();
    };

    DomManipulator.prototype.getSize = function(selector)
    {
        var size = {
            width: $(selector).width(),
            height: $(selector).height(),
            innerWidth: $(selector).innerWidth(),
            innerHeight: $(selector).innerHeight(),
            outerWidth: $(selector).outerWidth(),
            outerHeight: $(selector).outerHeight()
        };
        return size;
    }

    DomManipulator.prototype.getDimensions = function(selector)
    {
        var dimensions = this.getOffset(selector);
        var sizes = this.getSize(selector);
        for (var size in sizes) {
            dimensions[size] = sizes[size];
        }

        return dimensions;
    };

    DomManipulator.prototype.getParentDimensions = function(selector, parentSelector)
    {
        if (parentSelector) {
            parentSelector = $(selector).parents(parentSelector+':first');
        } else {
            parentSelector = $(selector).parent();
        }
        return this.getDimensions(parentSelector);
    };

    /**
     * This method will create a DOM element with the given classes applied,
     * get its dimensions, destroy it and then return the dimensions.
     *
     * This is useful for rendering when you need to account for elements that don't exist yet,
     * such as positioning template elements based on their wrapper element before it is rendered.
     *
     * Note: the temporary element created will have no content so its dimensions simply represent
     * its padding and border.
     * Note: the width and height given are for the whole element - the 'diameter' if you will. If you
     * want the 'radius' for positioning elements within one of these remember to half the dimensions.
     */
    DomManipulator.prototype.getDimensionsOfTemporaryElement = function(classes)
    {
        classes = (typeof classes !== 'array' ? classes.split(' ') : classes);
        var tempId = classes.join('-')+'-dimension-test';
        var tempHtml = '<div id="'+tempId+'" style="display:none" class="'+classes.join(' ')+'"/>';
        $('body').append(tempHtml);
        var dimensions = this.getDimensions('#'+tempId);
        $('#'+tempId).remove();
        return dimensions;
    };

    DomManipulator.prototype.getTemplateChangedEvent = function()
    {
        return DomManipulator.EVENT_TEMPLATE_CHANGED;
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

    DomManipulator.prototype.render = function(selector, html)
    {
        $(selector).html(html);
    };

    DomManipulator.prototype.setValue = function(selector, value)
    {
        $(selector).val(value);
    };

    DomManipulator.prototype.getValue = function(selector)
    {
        return $(selector).val();
    };

    return new DomManipulator();
});
