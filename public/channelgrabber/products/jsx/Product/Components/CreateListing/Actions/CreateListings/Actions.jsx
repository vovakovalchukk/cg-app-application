define([
], function(
) {
    "use strict";

    return {
        loadInitialValues: function(product, variationData, accounts, accountDefaultSettings, accountsData, categoryTemplates) {
            return {
                type: "LOAD_INITIAL_VALUES",
                payload: {
                    product: product,
                    variationData: variationData,
                    selectedAccounts: accounts,
                    accountDefaultSettings: accountDefaultSettings,
                    accountsData: accountsData,
                    categoryTemplates: categoryTemplates
                }
            };
        }
    };
});
