define([
    'ShippingAlias/DomManipulator',
    'EventCollator'
],
function(domManipulator, eventCollator)
{
    var DeleteButton = function() {
        var rootOuId;

        this.setRootOuId = function(newRootOuId)
        {
            rootOuId = newRootOuId;
            return this;
        };

        this.getRootOuId = function()
        {
            return rootOuId;
        };
    };

    DeleteButton.DELETE_BUTTON_SELECTOR = '.shipping-alias-delete';
    DeleteButton.DOM_SELECTOR_ALIAS = '.shipping-alias';
    DeleteButton.DOM_SELECTOR_ALIAS_NONE = '.shipping-alias-none';

    DeleteButton.prototype.init = function(rootOuId)
    {
        this.setRootOuId(rootOuId);
        var self = this;

        $(document).on("click", DeleteButton.DELETE_BUTTON_SELECTOR, function() {
            var rootOfThisAlias = $(this).closest('.shipping-alias').attr('id');
            var processedRootOfThisAlias = $('#' + rootOfThisAlias);

            if(processedRootOfThisAlias.find('input[name=shipping-alias-storedETag]').val() &&
                processedRootOfThisAlias.find('input[name=shipping-alias-id]').val()) {
                self.aliasDelete(rootOfThisAlias);
            }

            $(document).trigger(eventCollator.getEventRemoveFromQueue(), [
                'shippingAlias', rootOfThisAlias
            ]);

            processedRootOfThisAlias.remove();
            if ($(DeleteButton.DOM_SELECTOR_ALIAS).length == 0) {
                $(DeleteButton.DOM_SELECTOR_ALIAS_NONE).show();
            } else {
                domManipulator.updateAllAliasMethodCheckboxes();
            }
        });
    };

    DeleteButton.prototype.aliasDelete = function(alias)
    {
        var aliasInUse = $('#' + alias);
        var aliasID = aliasInUse.find('input[name=shipping-alias-id]').val();
        var storedETag = aliasInUse.find('input[name=shipping-alias-storedETag]').val();
        var singleAlias = {storedEtag: storedETag, id: aliasID, organisationUnitId: this.getRootOuId()};

        $.ajax({
            'url' : '/settings/shipping/alias/delete',
            'data' : {'alias' : JSON.stringify(singleAlias)},
            'method' : 'POST',
            'dataType' : 'json',
            'error' : function () {
                n.error('Unable to delete shipping aliases');
            }
        });
    };

    return new DeleteButton();
});
