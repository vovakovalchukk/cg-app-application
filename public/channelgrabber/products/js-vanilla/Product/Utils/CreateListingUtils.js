define([
], function(

) {
    "use strict";

    var CreateListingUtils = {
        productCanListToAccount: function(account) {
            return account.channel == 'ebay'
                && account.active
        }
    };

    return CreateListingUtils;
});