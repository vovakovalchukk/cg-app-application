define([
    'ShippingAlias/DomManipulator',
    'EventCollator'
],
    function(domManipulator, eventCollator)
    {
        var ShippingMethod = function() { };

        ShippingMethod.SELECT_BUTTON_SELECTOR = '.custom-select-item';

        ShippingMethod.prototype.init = function(module)
        {
            var self = this;

            $(document).on("click", ShippingMethod.SELECT_BUTTON_SELECTOR, function(e) {
                var dataValue = $(this).attr("data-value");
                var dataChecked = $(this).find("input").prop("checked");
                $(document).trigger(eventCollator.getRequestMadeEvent(), ['shippingAlias', $(this).closest('.shipping-alias').attr('id')]);
            });

            $(document).on(eventCollator.getQueueTimeoutEventPrefix() + 'shippingAlias', function(event, data) {
                var aliasInUse;
                for(aliasInUse in data) {
                    if(!$('#' + data[aliasInUse]).find('.inputbox').val()) {
                        n.error('Please set a shipping alias name');
                        return;
                    }
                    self.save(data[aliasInUse]);
                }
            });
        };

        ShippingMethod.prototype.save = function(alias)
        {
            var aliasInUse = $('#' + alias);
            var aliasID = aliasInUse.find('input[name=shipping-alias-id]').val();
            var aliasName = aliasInUse.find('.shipping-alias-name-holder .inputbox').val();
            var hiddenCheckBoxes = aliasInUse.find('.channel-shipping-methods input[type=hidden]');
            var checkBoxValues = [];

            hiddenCheckBoxes.each(function (index) {
                checkBoxValues[index] = $(this).val();
            });

            console.log(checkBoxValues);

            var singleAlias = {id: aliasID, name: aliasName, methodIds: checkBoxValues};

            $.ajax({
                'url' : '/settings/shipping/alias/save',
                'data' : {'alias' : JSON.stringify(singleAlias)},
                'method' : 'POST',
                'dataType' : 'json',
                'success' : function(data) {
                    aliasInUse.find('input[name=shipping-alias-id]').val(data.id);
                },
                'error' : function () {
                    n.error('Unable to save shipping aliases');
                }
            });
        };

        return new ShippingMethod();
    });
