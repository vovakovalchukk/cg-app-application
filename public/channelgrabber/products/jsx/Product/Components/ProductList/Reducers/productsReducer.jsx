define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/ProductList/Config/constants'
], function(
    reducerCreator,
    stateUtility,
    constants
) {
    "use strict";
    
    var initialState = {
        completeInitialLoads: {
            simpleAndParentProducts: false
        },
        simpleAndParentProducts: [],
        variationsByParent: [],
        allProductsLinks: {},
        visibleRows:[]
    };
    
    const {LINK_STATUSES} = constants;
    
    var ProductsReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            let newState = Object.assign({}, state, {
                completeInitialLoads: {
                    simpleAndParentProducts: true
                },
                simpleAndParentProducts: action.payload.products,
                visibleRows: action.payload.products
            });
            return newState;
        },
        "PRODUCT_LINKS_GET_REQUEST_SUCCESS": function(state, action) {
            console.log('in PRODUCT_LINKS_GET_REQUEST_sUCCESS action: ' , action);
            let skus = Object.keys(action.payload.formattedSkus);
            let newState = {};
            // todo - if the formattedSkus have productLinks already make these the same as the productLinks received
            if(skus.length > 1){
                newState = applyNewProductLinksToState(state, action.payload.productLinks)
                return newState;
            }
            
            newState = applySingleProductLinkChangeToState(state, action.payload.productLinks, skus[0]);
            return newState;
        },
        "STOCK_LEVELS_UPDATE_REQUEST_SUCCESS": function(state, action) {
            const {response} = action.payload;
            let productsCopy = state.simpleAndParentProducts.slice();
            let visibleRowsCopy = state.visibleRows.slice();
            let variationsCopy = Object.assign({}, state.variationsByParent);
            
            let newProducts = applyStockResponseToProducts(productsCopy, response);
            let newVisibleRows = applyStockResponseToProducts(visibleRowsCopy, response);
            let newVariations = applyStockResponseToVariations(visibleRowsCopy, variationsCopy, response);
            
            let newState = Object.assign({}, state, {
                simpleAndParentProducts: newProducts,
                visibleRows: newVisibleRows,
                variations: newVariations
            });
            
            return newState;
        },
        "PRODUCT_VARIATIONS_GET_REQUEST_SUCCESS": function(state, action) {
            let newVariationsByParent = Object.assign({}, state.variationsByParent, action.payload);
            let newState = Object.assign({}, state, {
                variationsByParent: newVariationsByParent
            });
            return newState;
        },
        "PRODUCT_EXPAND_REQUEST": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                action.payload.productRowIdToExpand,
                'loading'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "PRODUCT_EXPAND_SUCCESS": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowIdToExpand = action.payload.productRowIdToExpand;
            
            let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowIdToExpand);
            
            let rowsToAdd = state.variationsByParent[action.payload.productRowIdToExpand];
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                0,
                ...rowsToAdd
            );
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                action.payload.productRowIdToExpand,
                'expanded'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "PRODUCT_COLLAPSE": function(state, action) {
            let currentVisibleProducts = state.visibleRows.slice();
            let productRowId = action.payload.productRowIdToCollapse;
            
            let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowId);
            
            let numberOfRowsToRemove = state.variationsByParent[productRowId].length;
            
            currentVisibleProducts.splice(
                parentProductIndex + 1,
                numberOfRowsToRemove
            );
            
            currentVisibleProducts = changeExpandStatus(
                currentVisibleProducts,
                productRowId,
                'collapsed'
            );
            
            let newState = Object.assign({}, state, {
                visibleRows: currentVisibleProducts
            });
            return newState;
        },
        "FETCHING_LINKED_PRODUCTS_START": function(state, action) {
            let newState = applyLinksStatusChangesToProducts(
                state,
                action.payload.skusToFindLinkedProductsFor,
                LINK_STATUSES.fetching
            );
            return newState;
        },
        "FETCHING_LINKED_PRODUCTS_FINISH": function(state, action) {
            let newState = applyLinksStatusChangesToProducts(
                state,
                action.payload.skusToFindLinkedProductsFor,
                LINK_STATUSES.finishedFetching
            );
            return newState;
        }
    });
    
    return ProductsReducer;
    
    function applySingleProductLinkChangeToState(state,newLinks,sku){
        console.log('in applySingleProductLink.... state: ' , state);
        const normalizedNewLinks = normalizeLinks(newLinks);
        const stateLinksCopy = Object.assign({}, state.allProductsLinks);
        
        const productIdFromSku = stateUtility.getProductIdFromSku(state.visibleRows, sku);
        console.log('in applySingleProductLinkChangeToState with normalizedNewLinks: ' , normalizedNewLinks, ' stateLinksCopy: ' ,stateLinksCopy,' productIdFromSku: ', productIdFromSku);
        
        
        
        if(!normalizedNewLinks[productIdFromSku]){
            // todo no links set remove the productId fromt stateLinksCopy
            console.log('no newLinks for productsku so deleting what is there already');
            delete stateLinksCopy[productIdFromSku];
        }else{
            // if(!!stateLinksCopy[productIdFromSku]){
                console.log('productId exists on state so replacing whats there with normalizedNewLinks[productIdFromSku]:' , normalizedNewLinks[productIdFromSku]);
                //todo - set the link to the new response
                stateLinksCopy[productIdFromSku] = normalizedNewLinks[productIdFromSku];
            // }
        }
    
        
        console.log('newState to set : ' , stateLinksCopy);
        
        
        
        let newState = Object.assign({}, state, {
            allProductsLinks: stateLinksCopy
        });

        return newState;
    }
    
    function applyNewProductLinksToState(state, newLinks) {
        //todo go through a process of normalising from the start
        const normalizedNewLinks = normalizeLinks(newLinks);
        const stateLinksCopy = Object.assign({}, state.allProductsLinks);
        
        let newProductLinks = Object.assign({}, stateLinksCopy, normalizedNewLinks);
        // console.log('applyNewProductLinksToState state: ', state,  ' normalizedNewLinks ', normalizedNewLinks);
        // console.log('newNormalized: ', normalizedNewLinks, 'linkstobesetonstate: ' , newProductLinks);
    
        let newState = Object.assign({}, state, {
            allProductsLinks: newProductLinks
        });
        
        return newState;
        // const stateLinksCopy = Object.assign({}, state.allProductsLinks);
        //
        // let idsOfNewLinks = Object.keys(newLinks);
        //
        // let simpleAndVariationProductLinks = {};
        // Object.keys(stateLinksCopy).forEach(productId=>{
        //     if(idsOfNewLinks.indexOf(productId) < 0){
        //         return;
        //     }
        //     simpleAndVariationProductLinks[productId] = stateLinksCopy[productId];
        // });
        //
        // //todo loop over the simpleandvariationproductlinks - this a layer less...
        // Object.keys(simpleAndVariationProductLinks).forEach(productId=>{
        //    console.log('productId: ', productId, ' simpleAndVariationProductLinks[productId] : ' , simpleAndVariationProductLinks[productId]);
        //
        //    //todo need to merge the objects here if the exists in newLinks
        //
        // });
        //
        // let newProductLinks = Object.assign({},stateLinksCopy, newLinks);
        // let newState = Object.assign({},state, {
        //     allProductsLinks: newProductLinks
        // });
        // console.log('newState: ' , newState);
    }
    
    function normalizeLinks(links) {
        let simpleAndVariationLinks = {};
        Object.keys(links).forEach(productId => {
            if (isSimpleProductLink(links, productId)) {
                simpleAndVariationLinks[productId] = links[productId][productId];
                return;
            }
            let variationLinkObjects = links[productId];
            Object.keys(variationLinkObjects).forEach(productId => {
                simpleAndVariationLinks[productId] = variationLinkObjects[productId];
            });
        });
        return simpleAndVariationLinks;
    }
    
    function isSimpleProductLink(links,productId) {
        return Object.keys(links[productId]).length === 1 && !!links[productId][productId];
    }
    
    function applyLinksStatusChangesToProducts(state, skusToFindLinkedProductsFor, desiredLinkStatus) {
        let variationsByParentCopy = Object.assign({}, state.variationsByParent);
        let visibleRowsCopy = state.visibleRows.slice();
        
        let newVariationsByParent = applyFetchingStatusToVariations(
            variationsByParentCopy,
            skusToFindLinkedProductsFor,
            desiredLinkStatus
        );
        let newVisibleRows = applyFetchingStatusToNewVisibleRows(
            visibleRowsCopy,
            skusToFindLinkedProductsFor,
            desiredLinkStatus
        );
        
        return Object.assign({}, state, {
            variationsByParent: newVariationsByParent,
            visibleRows: newVisibleRows
        })
    }
    
    function changeExpandStatus(products, productId, desiredStatus) {
        let productRowIndex = products.findIndex((product) => {
            return product.id === productId;
        });
        products[productRowIndex].expandStatus = desiredStatus;
        return products;
    }
    
    function applyStockResponseToProducts(products, response) {
        products.forEach((product) => {
            if (product.variationCount !== 0) {
                return;
            }
            if (!response.stock[product.sku]) {
                return;
            }
            product.stock = response.stock[product.sku];
        });
        return products;
    }
    
    function applyStockResponseToVariations(products, variations, response) {
        products.forEach((product) => {
            if (product.variationCount == 0 || !variations[product.id]) {
                return;
            }
            variations[product.id].forEach(function(product) {
                if (!response.stock[product.sku]) {
                    return;
                }
                product.stock = response.stock[product.sku];
                return;
            });
        });
        return variations;
    }
    
    function applyFetchingStatusToVariations(variationsByParent, skusToFindLinkedProductsFor, DESIRED_LINK_STATUS) {
        Object.keys(variationsByParent).map(parentId => {
            let variations = variationsByParent[parentId];
            variations.forEach((variation, i) => {
                if (skusToFindLinkedProductsFor.indexOf(variation.sku) < 0) {
                    return;
                }
                variationsByParent[parentId][i]["linkStatus"] = DESIRED_LINK_STATUS
            });
        });
        return variationsByParent;
    }
    
    function applyFetchingStatusToNewVisibleRows(visibleRowsCopy, skusToFindLinkedProductsFor, DESIRED_LINK_STATUS) {
        // console.log('in applyFetchingStatusToNewVisibleRows visibleRowsCopy: ' , visibleRowsCopy, ' skusToFindLinkedProductsFor: ' , skusToFindLinkedProductsFor);
        visibleRowsCopy.forEach((row, i) => {
            if (skusToFindLinkedProductsFor.indexOf(row.sku) < 0) {
                return;
            }
            visibleRowsCopy[i]["linkStatus"] = DESIRED_LINK_STATUS;
        });
        return visibleRowsCopy;
    }
});