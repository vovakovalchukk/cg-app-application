define([
], function(

) {
    "use strict";

    var allowedChannels = ['ebay', 'shopify'];

    var CreateListingUtils = {
        productCanListToAccount: function(account, accountsProductIsListedOn) {
            return (allowedChannels.indexOf(account.channel) >= 0)
                && account.active
                && !accountsProductIsListedOn.includes(account.id.toString());
        }
    };

    return CreateListingUtils;
});