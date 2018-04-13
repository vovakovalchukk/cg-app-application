define([], function() {
    "use strict";

    return {
        buildCategoryChildrenUrl: function (accountId, categoryId) {
            return '/settings/category/templates/' + accountId + '/category-children/' + categoryId;
        },
        buildRefreshCategoryUrl: function(accountId) {
            return '/settings/category/templates/' + accountId + '/refresh-categories';
        },
        buildFetchCategoryMapsUrl: function() {
            return '/settings/category/templates/fetch';
        },
        buildDeleteCategoryMapUrl: function(mapId) {
            return '/settings/category/templates/' + mapId + '/delete';
        },
        buildSaveCategoryMapUrl: function() {
            return '/settings/category/templates/save';
        }
    };
});
