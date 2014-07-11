define([
    'jquery',
    'cg-mustache',
    'ShippingAlias/MethodCollection'
], function(
    $,
    CGMustache,
    methodCollection
) {
    var DomManipulator = function()
    {

    };

    DomManipulator.ALIAS_CHANGED = 'alias-changed';
    DomManipulator.ALIAS_DELETED = 'alias-deleted';
    DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER = '#shipping-alias-container';
    DomManipulator.SHIPPING_METHOD_SELECTOR = '.channel-shipping-methods .custom-select-item';

    DomManipulator.prototype.prependAlias = function()
    {
        var self = this;
        var aliasUrlMap = {
            text: '/channelgrabber/zf2-v4-ui/templates/elements/text.mustache',
            deleteButton: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            multiSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select-group.mustache',
            multiSelectExpanded: '/channelgrabber/zf2-v4-ui/templates/elements/multiselectexpanded.mustache',
            alias: '/channelgrabber/settings/template/ShippingAlias/alias.mustache'
        };
        CGMustache.get().fetchTemplates(aliasUrlMap, function(templates, cgmustache)
        {
            var aliasNo = $('.shipping-alias').length + 1;
            var text = cgmustache.renderTemplate(templates, {'name': "alias-name-" + aliasNo}, "text");
            var deleteButton = cgmustache.renderTemplate(templates, {
                'buttons' : true,
                'value' : "Delete",
                'id' : "deleteButton-" + aliasNo
            }, "deleteButton");

            var multiSelect = cgmustache.renderTemplate(templates, {'options': methodCollection.getItems(),
                    'name': 'aliasMultiSelect-' + aliasNo}, "multiSelect");
            var multiSelectExpanded = cgmustache.renderTemplate(templates, {}, "multiSelectExpanded", {'multiSelect' : multiSelect});
            var alias = cgmustache.renderTemplate(templates, {
                'id' : 'shipping-alias-' + aliasNo
            }, "alias", {
                'multiSelectExpanded' : multiSelectExpanded,
                'deleteButton' : deleteButton,
                'text' : text
            });

            self.prepend(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER, alias);
        });
    };

    DomManipulator.prototype.updateOtherAliasMethodCheckboxes = function(selectedElement)
    {
        var checked = $(selectedElement).find('input:checkbox').is(':checked');
        var value = $(selectedElement).data('value');
        var matchingElements = $(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER + ' ' + DomManipulator.SHIPPING_METHOD_SELECTOR + '[data-value='+value+']');
        
        matchingElements.each(function()
        {
            if (this === selectedElement) {
                // Continue
                return true;
            }

            var currentCheckbox = $(this).find('input:checkbox');
            if (checked) {
                if (currentCheckbox.is(':checked')) {
                    currentCheckbox.click();
                }
                currentCheckbox.addClass('disabled');
            }
        });
    };

    DomManipulator.prototype.updateAllAliasMethodCheckboxes = function()
    {
        var self = this;
        var checkedCheckboxes = $(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER + ' ' + DomManipulator.SHIPPING_METHOD_SELECTOR + ' input:checked');
        checkedCheckboxes.each(function()
        {
            var checkedElement = checkedCheckboxes.closest(DomManipulator.SHIPPING_METHOD_SELECTOR);
            self.updateOtherAliasMethodCheckboxes(checkedElement);
        });
    };

    DomManipulator.prototype.remove = function(id, html)
    {
        $(id).remove(html);
    };

    DomManipulator.prototype.prepend = function(id, html)
    {
        $(id).prepend(html);
    };

    DomManipulator.prototype.getDomSelectorAliasContainer = function()
    {
        return DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER;
    }

    return new DomManipulator();
});
