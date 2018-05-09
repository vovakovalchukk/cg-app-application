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
        }
    };
});
