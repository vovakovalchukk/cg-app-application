define([], function() {
    "use strict";

    return {
        categoryRootsFetched: function(response) {
            return {
                type: "CATEGORY_ROOTS_FETCHED",
                payload: {
                    accountCategories: response.accountCategories
                }
            };
        },
        noAccountSettings: function(accountId) {
            return {
                type: "NO_ACCOUNT_SETTINGS",
                payload: {
                    accountId: accountId
                }
            };
        },
        accountSettingsFetched: function (accountId, response) {
            if ('bodyTag' in response) {
                delete response.bodyTag;
            }

            return {
                type: "ACCOUNT_SETTINGS_FETCHED",
                payload: {
                    accountId: accountId,
                    settings: response
                }
            };
        }
    };
});
