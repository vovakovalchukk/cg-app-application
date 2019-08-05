import constants from 'Product/Components/ProductList/Config/constants';

"use strict";

const {PRODUCT_LINKS_URL} = constants;

var actionCreators = (function() {
    const getProductLinksSuccess = (productLinks, formattedSkus) => {
        return {
            type: "PRODUCT_LINKS_GET_REQUEST_SUCCESS",
            payload: {
                productLinks,
                formattedSkus
            }
        }
    };
    const getProductLinksRequest = (skusToFindLinkedProductsFor) => {
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
    const fetchingProductLinksFinish = (skusToFindLinkedProductsFor) => {
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
                if (!state.accounts.getFeatures(state).linkedProducts) {
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
                    dispatch(getProductLinksSuccess(response.productLinks, formattedSkus));
                    dispatch(fetchingProductLinksFinish(skusToFindLinkedProductsFor));
                } catch (error) {
                    console.warn(error);
                }
            }
        },
    };
})();

export default actionCreators;

function getSkusToFindLinkedProductsFor(products) {
    let skusToFindLinkedProductsFor = [];
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