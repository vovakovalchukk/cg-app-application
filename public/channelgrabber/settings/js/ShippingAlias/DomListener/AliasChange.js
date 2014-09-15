define([
    'ShippingAlias/DomManipulator',
    'EventCollator'
],
function(domManipulator, eventCollator)
{
    var AliasChange = function() {
        var rootOuId;
        var savePipe = [];

        this.setRootOuId = function(newRootOuId)
        {
            rootOuId = newRootOuId;
            return this;
        };

        this.getRootOuId = function()
        {
            return rootOuId;
        };

        this.getSavePipe = function(alias)
        {
            if (!(alias in savePipe)) {
                savePipe[alias] = domManipulator.deferred();
            }

            return savePipe[alias];
        };

        this.getDomManipulator = function()
        {
            return domManipulator;
        };
    };

    AliasChange.SHIPPING_METHOD_SELECTOR = '.channel-shipping-methods .custom-select-item';
    AliasChange.ALIAS_NAME_INPUT_SELECTOR = '.shipping-alias-name-holder .inputbox';
    AliasChange.SHIPPING_SERVICES_CUSTOM_SELECT_SELECTOR = '.shipping-services';

    AliasChange.prototype.init = function(rootOuId)
    {
        this.setRootOuId(rootOuId);
        var self = this;

        $(document).on("click", AliasChange.SHIPPING_METHOD_SELECTOR, function() {
            self.getDomManipulator().updateOtherAliasMethodCheckboxes(this);
            self.triggerRequestMadeEvent(this);
        });

        $(document).on("click", AliasChange.SHIPPING_SERVICES_CUSTOM_SELECT_SELECTOR, function() {
            self.triggerRequestMadeEvent(this);
        });

        $(document).on("keyup", AliasChange.ALIAS_NAME_INPUT_SELECTOR, function(event, data) {
            self.triggerRequestMadeEvent(this);
        });

        $(document).on(eventCollator.getQueueTimeoutEventPrefix() + 'shippingAlias', function(event, data) {
            self.validateAndSaveAliases(data);
        });
    };

    AliasChange.prototype.triggerRequestMadeEvent = function(domElement)
    {
        var unique = true;
        $(document).trigger(eventCollator.getRequestMadeEvent(), [
            'shippingAlias', $(domElement).closest('.shipping-alias').attr('id'), unique
        ]);
    };

    AliasChange.prototype.validateAndSaveAliases = function(aliasDomIds)
    {
        var self = this;
        var aliasNameVal;
        for(var index in aliasDomIds) {
            aliasNameVal = $('#' + aliasDomIds[index]).find(AliasChange.ALIAS_NAME_INPUT_SELECTOR).val();
            aliasNameVal = aliasNameVal.trim();
            if(!aliasNameVal) {
                n.error('Please set a shipping alias name');
                return;
            }
            this.getSavePipe(aliasDomIds[index]).then(function() {
                return self.save(aliasDomIds[index]);
            });
        }
    };

    AliasChange.prototype.save = function(alias)
    {
        var aliasInUse = $('#' + alias);
        var aliasID = aliasInUse.find('input[name=shipping-alias-id]').val();
        var storedETag = aliasInUse.find('input[name=shipping-alias-storedETag]').val();
        var aliasName = aliasInUse.find('.shipping-alias-name-holder .inputbox').val();
        var aliasAccount = aliasInUse.find('input[class=shipping-account-select][type=hidden]').val();
        var aliasService = aliasInUse.find('input[class=shipping-service-select][type=hidden]').val();
        var hiddenCheckBoxes = aliasInUse.find('.channel-shipping-methods input[type=hidden]');
        var checkBoxValues = [];

        if(aliasService === undefined) {
            aliasService = '';
        }

        hiddenCheckBoxes.each(function (index) {
            checkBoxValues[index] = $(this).val();
        });

        var singleAlias = {
            storedEtag: storedETag,
            id: aliasID,
            name: aliasName,
            organisationUnitId: this.getRootOuId(),
            accountId: aliasAccount,
            shippingService: aliasService,
            methodIds: checkBoxValues
        };

        return $.ajax({
            'url' : '/settings/shipping/alias/save',
            'data' : {'alias' : JSON.stringify(singleAlias)},
            'method' : 'POST',
            'dataType' : 'json',
            'success' : function(data) {
                if(data.hasOwnProperty('alias')) {
                    var parsedData = $.parseJSON(data['alias']);
                    aliasInUse.find('input[name=shipping-alias-id]').val(parsedData.id);
                    aliasInUse.find('input[name=shipping-alias-storedETag]').val(parsedData.storedETag);
                }
            },
            'error' : function () {
                n.error('Unable to save shipping aliases');
            }
        });
    };

    return new AliasChange();
});
