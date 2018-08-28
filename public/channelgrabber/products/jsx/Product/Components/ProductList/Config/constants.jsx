define([
], function(
) {
    "use strict";
    
    console.log('in constants.jsx');
    
    return {
        PRODUCTS_URL : "/products/ajax",
        PRODUCT_LINKS_URL : "/products/links/ajax",
        LINK_STATUSES : {
            fetching: "fetching",
            success: "success"
        },
    };
});