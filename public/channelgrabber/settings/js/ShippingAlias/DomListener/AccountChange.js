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
            var aliasId = e.target.id.split('-');
            aliasId = aliasId[aliasId.length - 1];
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

    AccountChange.prototype.populateServicesCustomSelect = function()
    {
        $('input[class=shipping-account-select][type=hidden]').each(function(){
            var services = self.fetchServices(this.val(), function(services){
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
    }

    AccountChange.prototype.fetchServices = function(accountId, callback)
    {
        $.ajax({
            'url': '/settings/shipping/services/fetch/' + accountId,
            'method': 'GET',
            'success': function(data) {
                callback(data['shippingServices']);

            },
            'error': function() {
                n.error('An error has occurred. Please try again.');
                callback(null);
            }
        });
    }

    return new AccountChange();
});