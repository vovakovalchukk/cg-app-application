define([
    'Product/Storage/Ajax',
    'Product/Filter/Entity',
    'Product/Components/ProductList/Config/constants'
], function(
    AjaxHandler,
    ProductFilter,
    constants
) {
    "use strict";
    
    const {PRODUCT_LINKS_URL} = constants;
    
    var actionCreators = (function() {
        let self = {};
        
        const getProductLinksSuccess = (productLinks) => {
            return {
                type: "PRODUCT_LINKS_GET_REQUEST_SUCCESS",
                payload: {
                    productLinks
                }
            }
        };
        const getProductLinksRequest = (skusToFindLinkedProductsFor) => {
            // console.log('in getProductLinksRequest - AC with skusToFindLinkedProductsFor: ', skusToFindLinkedProductsFor);
            return $.ajax({
                url: PRODUCT_LINKS_URL,
                data: {
                    skus: JSON.stringify(skusToFindLinkedProductsFor)
                },
                type: 'POST'
            });
        };
        const fetchingProductLinksStart = (skusToFindLinkedProductsFor) => {
            return {
                type: "FETCHING_LINKED_PRODUCTS_START",
                payload: {
                    skusToFindLinkedProductsFor
                }
            }
        };
        const fetchingProductLinksFinish = (skusToFindLinkedProductsFor)=>{
            return {
                type: "FETCHING_LINKED_PRODUCTS_FINISH",
                payload: {
                    skusToFindLinkedProductsFor
                }
            }
        };
        
        return {
            getLinkedProducts: (productSkus) => {
                return async function(dispatch, getState) {
                    let state = getState();
                    if (!state.account.features.linkedProducts) {
                        return;
                    }
                    
                    let skusToFindLinkedProductsFor = [];
                    if (!productSkus) {
                        skusToFindLinkedProductsFor = getSkusToFindLinkedProductsFor(state.products);
                    } else {
                        skusToFindLinkedProductsFor = productSkus;
                    }
    
                    dispatch(fetchingProductLinksStart(skusToFindLinkedProductsFor));
                    let formattedSkus = formatSkusForLinkApi(skusToFindLinkedProductsFor);
                    
                    try {
                        let response = await getProductLinksRequest(formattedSkus);
                        dispatch(getProductLinksSuccess(response.productLinks));
                        dispatch(fetchingProductLinksFinish(skusToFindLinkedProductsFor));
                    } catch (error) {
                        console.warn(error);
                    }
                }
            },
        };
        
    })();
    
    return actionCreators;
    
    function getSkusToFindLinkedProductsFor(products) {
        var skusToFindLinkedProductsFor = [];
        products.visibleRows.forEach((product) => {
            if (product.sku) {
                skusToFindLinkedProductsFor.push(product.sku);
            }
        });
        return skusToFindLinkedProductsFor;
    }
    
    function formatSkusForLinkApi(skusToFindLinkedProductsFor) {
        let linkObj = {};
        skusToFindLinkedProductsFor.forEach((sku) => {
            linkObj[sku] = sku;
        });
        return linkObj;
    }
});