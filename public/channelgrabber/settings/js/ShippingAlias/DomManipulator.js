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

    DomManipulator.ALIAS_CHANGED = 'alias-changed';
    DomManipulator.ALIAS_DELETED = 'alias-deleted';
    DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER = '#shipping-alias-container';

    DomManipulator.prototype.prependAlias = function()
    {
        console.log("DomManipulator prependAlias1"); 
        console.log("DomManipulator prependAlias1");
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
            var text = cgmustache.renderTemplate(templates, {}, "text");
            var deleteButton = cgmustache.renderTemplate(templates, {
                'buttons' : true,
                'value' : "Delete",
                'id' : "deleteButton"
            }, "deleteButton");

            var multiSelect = cgmustache.renderTemplate(templates, {}, "multiSelect");
            var multiSelectExpanded = cgmustache.renderTemplate(templates, {}, "multiSelectExpanded", {'multiSelect' : multiSelect});
            var alias = cgmustache.renderTemplate(templates, {}, "alias", {
                'multiSelectExpanded' : multiSelectExpanded,
                'deleteButton' : deleteButton,
                'text' : text
            });

            self.prepend(DomManipulator.DOM_SELECTOR_ALIAS_CONTAINER, alias);
        });
    };

    DomManipulator.prototype.prepend = function(id, html)
    {
        $(id).prepend(html);
    };

    return new DomManipulator();
});
