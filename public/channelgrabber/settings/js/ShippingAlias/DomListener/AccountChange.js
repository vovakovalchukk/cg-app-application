define([
    'ShippingAlias/DomManipulator'
],
function(domManipulator)
{
    var AccountChange = function() { };

    AccountChange.ACCOUNT_SELECTOR = '[id^=shipping-account-custom-select-]';

    AccountChange.prototype.init = function(module)
    {
        var self = this;
        $(document).on('change', AccountChange.ACCOUNT_SELECTOR, function(e){
            var accountId = $('#' + e.target.id).find('input[class=shipping-account-select][type=hidden]').val();
            console.log(accountId);
            var aliasId = e.target.id.split('-');
            aliasId = aliasId[aliasId.length - 1];
            var services = self.fetchServices(accountId);
            domManipulator.updateServicesCustomSelect(aliasId, services);
        });

    };

    AccountChange.prototype.fetchServices = function(accountId)
    {
        $.ajax({
            'url': '/settings/shipping/services/fetch/'+accountId,
            'method': 'GET',
            'success': function(data) {

            },
            'error': function() {

            }
        });
    }

    return new AccountChange();
});