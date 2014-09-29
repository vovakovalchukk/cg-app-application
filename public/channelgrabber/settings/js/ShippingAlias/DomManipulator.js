define([
    'jquery',
    'cg-mustache',
    'ShippingAlias/MethodCollection',
    'ShippingAlias/AccountCollection'
], function(
    $,
    CGMustache,
    methodCollection,
    accountCollection
) {
    var DomManipulator = function()
    {
        var aliasNo = 0;

        this.getAliasNo = function()
        {
            return aliasNo;
        };

        this.getAndIncrementAliasNo = function()
        {
            return ++aliasNo;
        };
    };

    DomManipulator.ALIAS_CHANGED = 'alias-changed';
    DomManipulator.ALIAS_DELETED = 'alias-deleted';
    DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER = '#shipping-alias-container';
    DomManipulator.DOM_SELECTOR_ALIAS = '.shipping-alias';
    DomManipulator.DOM_SELECTOR_ALIAS_NONE = '.shipping-alias-none';
    DomManipulator.SHIPPING_METHOD_SELECTOR = '.channel-shipping-methods .custom-select-item';

    DomManipulator.prototype.prependAlias = function()
    {
        var self = this;
        var aliasUrlMap = {
            text: '/channelgrabber/zf2-v4-ui/templates/elements/text.mustache',
            deleteButton: '/channelgrabber/zf2-v4-ui/templates/elements/buttons.mustache',
            multiSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select-group.mustache',
            multiSelectExpanded: '/channelgrabber/zf2-v4-ui/templates/elements/multiselectexpanded.mustache',
            alias: '/channelgrabber/settings/template/ShippingAlias/alias.mustache',
            customSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache'
        };
        CGMustache.get().fetchTemplates(aliasUrlMap, function(templates, cgmustache)
        {
            var aliasNo = self.getAndIncrementAliasNo();
            var text = cgmustache.renderTemplate(templates, {'name': "alias-name-" + aliasNo}, "text");
            var deleteButton = cgmustache.renderTemplate(templates, {
                'buttons' : true,
                'value' : "Delete",
                'id' : "deleteButton-" + aliasNo
            }, "deleteButton");

            var multiSelect = cgmustache.renderTemplate(templates, {'options': methodCollection.getItems(),
                    'name': 'aliasMultiSelect-' + aliasNo}, "multiSelect");
            var multiSelectExpanded = cgmustache.renderTemplate(templates, {}, "multiSelectExpanded", {'multiSelect' : multiSelect});

            var accountCustomSelect = cgmustache.renderTemplate(templates, {
                isOptional: 'true',
                id: 'shipping-account-custom-select-'+aliasNo,
                name: 'shipping-account-custom-select-'+aliasNo,
                class: 'shipping-account-select',
                options: accountCollection.getItems()
            }, "customSelect");

            var alias = cgmustache.renderTemplate(templates, {'id' : 'shipping-alias-new-' + aliasNo}, "alias", {
                'multiSelectExpanded' : multiSelectExpanded,
                'accountCustomSelect': accountCustomSelect,
                'deleteButton' : deleteButton,
                'text' : text
            });

            if ($(DomManipulator.DOM_SELECTOR_ALIAS_NONE).is(':visible')) {
                $(DomManipulator.DOM_SELECTOR_ALIAS_NONE).hide();
            }
            self.prepend(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER, alias);
            self.updateAllAliasMethodCheckboxes();
        });
    };

    DomManipulator.prototype.updateOtherAliasMethodCheckboxes = function(selectedElement)
    {
        var selectedAliasDomId = $(selectedElement).closest(DomManipulator.DOM_SELECTOR_ALIAS).attr('id');
        var selectedChecked = $(selectedElement).find('input:checkbox').is(':checked');
        var selectedValue = $(selectedElement).data('value');
        var matchingElements = $(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER + ' ' + DomManipulator.SHIPPING_METHOD_SELECTOR + '[data-value='+selectedValue+']');

        var anyChecked = selectedChecked;
        if (selectedChecked) {
            $(selectedElement).find('input:checkbox').removeClass('disabled');
        } else {
            matchingElements.each(function()
            {
                var currentCheckbox = $(this).find('input:checkbox');
                if (currentCheckbox.is(':checked')) {
                    anyChecked = true;
                    // break
                    return false;
                }
            });
        }

        matchingElements.each(function()
        {
            var aliasDomId = $(this).closest(DomManipulator.DOM_SELECTOR_ALIAS).attr('id');
            if (aliasDomId === selectedAliasDomId) {
                // Continue
                return true;
            }

            var currentCheckbox = $(this).find('input:checkbox');
            if (selectedChecked) {
                if (currentCheckbox.is(':checked')) {
                    $(this).click();
                }
                currentCheckbox.addClass('disabled');
            } else if (!anyChecked) {
                currentCheckbox.removeClass('disabled');
            }
        });
    };

    DomManipulator.prototype.updateAllAliasMethodCheckboxes = function()
    {
        var self = this;
        // Reset
        var disabledCheckboxes = $(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER + ' ' + DomManipulator.SHIPPING_METHOD_SELECTOR + ' input.disabled');
        disabledCheckboxes.removeClass('disabled');

        var checkedCheckboxes = $(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER + ' ' + DomManipulator.SHIPPING_METHOD_SELECTOR + ' input:checked');
        checkedCheckboxes.each(function()
        {
            var checkedElement = $(this).closest(DomManipulator.SHIPPING_METHOD_SELECTOR);
            self.updateOtherAliasMethodCheckboxes(checkedElement);
        });
    };

    DomManipulator.prototype.updateServicesCustomSelect = function(aliasId, services)
    {
        if(services.length === 0) {
            if($("#shipping-alias-" + aliasId).length) {
                $("#shipping-alias-" + aliasId).find("#services-custom-select").html('');
            } else if($("#shipping-alias-new-" + aliasId).length) {
                $("#shipping-alias-new-" + aliasId).find("#services-custom-select").html('');
            }
            return;
        }

        var aliasUrlMap = {
            customSelect: '/channelgrabber/zf2-v4-ui/templates/elements/custom-select.mustache'
        };
        CGMustache.get().fetchTemplates(aliasUrlMap, function(templates, cgmustache)
        {
            var serviceCustomSelect = cgmustache.renderTemplate(templates, {
                isOptional: 'true',
                id: 'shipping-service-custom-select-' + aliasId,
                name: 'shipping-service-custom-select-' + aliasId,
                class: 'shipping-service-select',
                options: services
            }, "customSelect");

            if($("#shipping-alias-" + aliasId).length) {
                $("#shipping-alias-" + aliasId).find("#services-custom-select").html(serviceCustomSelect);
            } else if($("#shipping-alias-new-" + aliasId).length) {
                $("#shipping-alias-new-" + aliasId).find("#services-custom-select").html(serviceCustomSelect);
            }
        });
    }

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
