define([
], function(

) {
    "use strict";

    var CreateListingUtils = {
        productCanListToAccount: function(account, allowedChannels) {
            return allowedChannels && (account.channel in allowedChannels) && account.active;
        }
    };

    return CreateListingUtils;
});