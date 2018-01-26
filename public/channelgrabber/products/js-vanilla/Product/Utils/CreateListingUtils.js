define([
], function(

) {
    "use strict";

    var allowedChannels = ['ebay', 'shopify'];

    var CreateListingUtils = {
        productCanListToAccount: function(account) {
            return (allowedChannels.indexOf(account.channel) >= 0)
                && account.active;
        }
    };

    return CreateListingUtils;
});