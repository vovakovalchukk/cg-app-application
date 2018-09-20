define([
], function(
) {
    "use strict";
    
    let constants = {
        PRODUCTS_URL : "/products/ajax",
        PRODUCT_LINKS_URL : "/products/links/ajax",
        IMAGE_DIR: 'cg-built/products/img/',
        LINK_STATUSES : {
            fetching: "fetching",
            finishedFetching: "finishedFetching"
        },
        STOCK_MODE_EDITING_STATUSES:{
            editing:'editing'
        }
    };
    constants.ADD_ICON_URL = constants.IMAGE_DIR + 'add-icon.png';
    
    return constants;
});