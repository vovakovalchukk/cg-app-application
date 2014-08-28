define([
    'jquery',
    'ShippingAlias/DomManipulator'
],
function($,domManipulator)
{
    var AccountChange = function() { };

    AccountChange.ACCOUNT_SELECTOR = '[id^=shipping-account-custom-select-]';

    AccountChange.prototype.init = function(module)
    {
        var self = this;
        $(document).on('change', AccountChange.ACCOUNT_SELECTOR, function(e){
            var accountId = $(this).find('input[class=shipping-account-select][type=hidden]').val();
            var aliasId = e.target.id.split('-').pop();
            var services = self.fetchServices(accountId, function(services){
                if(services !== null) {
                    var servicesOptions = [];
                    for (var service in services) {
                        if(services.hasOwnProperty(service)) {
                            servicesOptions.push({title:services[service], value: service});
                        }
                    }
                    domManipulator.updateServicesCustomSelect(aliasId, servicesOptions);
                }
            });
        });
    };

    AccountChange.prototype.fetchServices = function(accountId, callback)
    {
        $.ajax({
            'url': '/settings/shipping/services/' + accountId,
            'method': 'GET',
            'success': function(data) {
                callback(data['shippingServices']);

            },
            'error': function() {
                n.error('An error has occurred. Please try again.');
                callback(null);
            }
        });
    };

    return new AccountChange();
});