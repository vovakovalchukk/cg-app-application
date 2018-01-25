define([
], function(

) {
    "use strict";

    var CreateListingUtils = {
        productCanListToAccount: function(account, accountsProductIsListedOn) {
            return account.channel == 'ebay'
                && account.active
                && !accountsProductIsListedOn.includes(account.id.toString());
        }
    };

    return CreateListingUtils;
});