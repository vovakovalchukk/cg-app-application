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
        getLinkedProducts: (productSkus, links) => {
//            console.log('in getLinkedProducts with productSkus: ' , productSkus);
//            console.trace();
//            todo - fix this - make the skus update for the corresponding links....

            return async function(dispatch, getState) {
                let state = getState();
                if (!state.accounts.getFeatures(state).linkedProducts) {
                    return;
                }


                  let  skusToFindLinkedProductsFor = getSkusToFindLinkedProductsFor(productSkus, state.products, links);
                
//                console.log('skusToFindLinkedProductsFor: ', skusToFindLinkedProductsFor);
                
//                    skusToFindLinkedProductsFor = skusToFetchLinksFor;
//                }
//
//                console.log('skusToFind: ', skusToFind);
//

                dispatch(fetchingProductLinksStart(skusToFindLinkedProductsFor));
                
                let formattedSkus = formatSkusForLinkApi(skusToFindLinkedProductsFor);
                try {
//                    console.log('formattedSkus to send to api: ', formattedSkus);

                    let response = await getProductLinksRequest(formattedSkus);
//                    console.log(' response: ',  response);
                    
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

function getSkusFromLinks(links ){
    let resultingSkus = [];

    if(!links){
        return resultingSkus;
    }

    links.forEach(link=>{
        resultingSkus.push(link.sku);
    });
    return resultingSkus;
}

function getSkusToFindLinkedProductsFor(productSkus, products, links) {
    let skusToFindLinkedProductsFor = [];
    if(!productSkus){
        products.visibleRows.forEach((product) => {
            if (product.sku) {
                skusToFindLinkedProductsFor.push(product.sku);
            }
        });
        return skusToFindLinkedProductsFor;
    }
    skusToFindLinkedProductsFor = productSkus;
    if(links){
        let skusFromLinks = getSkusFromLinks(links);
        console.log('skusFromLinks: ', skusFromLinks);

        skusToFindLinkedProductsFor = skusToFindLinkedProductsFor.concat(skusFromLinks);
    }
    return skusToFindLinkedProductsFor;
}

function formatSkusForLinkApi(skusToFindLinkedProductsFor) {
    let linkObj = {};
    skusToFindLinkedProductsFor.forEach((sku) => {
        linkObj[sku] = sku;
    });
    return linkObj;
}