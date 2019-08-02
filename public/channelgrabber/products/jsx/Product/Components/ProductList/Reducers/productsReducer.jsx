import utility from "Product/Components/ProductList/utility";
import reducerCreator from 'Common/Reducers/creator';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import constants from 'Product/Components/ProductList/Config/constants';

"use strict";

var initialState = {
    completeInitialLoads: {
        simpleAndParentProducts: false
    },
    simpleAndParentProducts: [],
    variationsByParent: [],
    allProductsLinks: {},
    visibleRows: [],
    haveFetched: false,
    fetching: false
};

const {LINK_STATUSES, EXPAND_STATUSES} = constants;

var ProductsReducer = reducerCreator(initialState, {
    "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
        let productsWithListings = applyListingsToProducts(action.payload.products, action.payload.listings);
        let newState = Object.assign({}, state, {
            completeInitialLoads: {
                simpleAndParentProducts: true
            },
            simpleAndParentProducts: action.payload.products,
            visibleRows: action.payload.products,
            haveFetched: true,
            fetching: false
        });
        return newState;
    },
    "PRODUCTS_GET_REQUEST_START": function(state) {
        let newState = Object.assign({}, state, {
            fetching: true
        });
        return newState;
    },
    "PRODUCTS_GET_REQUEST_ERROR": function() {
        let newState = Object.assign({}, state, {
            fetching: false
        });
        return newState;
    },
    "PRODUCT_LINKS_GET_REQUEST_SUCCESS": function(state, action) {
        let skus = Object.keys(action.payload.formattedSkus);
        let newState = {};
        if (skus.length > 1) {
            newState = applyNewProductLinksToState(state, action.payload.productLinks);
            return newState;
        }
        newState = applySingleProductLinkChangeToState(state, action.payload.productLinks, skus[0]);
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
        currentVisibleProducts = changeExpandStatusForId(
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
        let {productIdToExpand} = action.payload;

        currentVisibleProducts = addRowsForSingleProductExpansion(currentVisibleProducts, productIdToExpand, state);

        let newState = Object.assign({}, state, {
            visibleRows: currentVisibleProducts
        });
        return newState;
    },
    "PRODUCTS_EXPAND_SUCCESS": function(state, action) {
        let currentVisibleProducts = state.visibleRows.slice();
        let {productIdsToExpand} = action.payload;

        for (let id of productIdsToExpand) {
            let parentProduct = stateUtility.getProductById(currentVisibleProducts, id);

            if(parentProduct.expandStatus === EXPAND_STATUSES.expanded){
                continue;
            }

            currentVisibleProducts = addRowsForSingleProductExpansion(currentVisibleProducts, id, state);
        }

        let newState = Object.assign({}, state, {
            visibleRows: currentVisibleProducts
        });
        return newState;
    },
    "ALL_PRODUCTS_COLLAPSE": function(state, action) {
        let currentVisibleProducts = state.visibleRows.slice();
        let parentProductIds = stateUtility.getAllParentProductIds(state);

        for (let id of parentProductIds) {
            let parentProduct = stateUtility.getProductById(currentVisibleProducts, id);

            if(parentProduct.expandStatus === EXPAND_STATUSES.collapsed){
                continue;
            }

            currentVisibleProducts = collapseSingleProduct(currentVisibleProducts, id, state);
        }

        let newState = Object.assign({}, state, {
            visibleRows: currentVisibleProducts
        });
        return newState;
    },
    "PRODUCT_COLLAPSE": function(state, action) {
        let currentVisibleProducts = state.visibleRows.slice();
        let productRowId = action.payload.productRowIdToCollapse;

        currentVisibleProducts = collapseSingleProduct(currentVisibleProducts, productRowId, state);

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
    },
    "PRODUCTS_DELETE_SUCCESS": function(state, action) {
        let {deletedProducts} = action.payload;
        let visibleRowIds = state.visibleRows.map(row => (row.id));
        let idsOfRowsToKeep = utility.findDifferenceOfTwoArrays(visibleRowIds, deletedProducts);

        let newVisibleRows = state.visibleRows.slice();

        let leftoverRows = [];
        newVisibleRows.forEach(row => {
            if (idsOfRowsToKeep.includes(row.id)) {
                return leftoverRows.push(row);
            }
        });

        return Object.assign({}, state, {
            visibleRows: leftoverRows
        });
    },
    "AVAILABLE_UPDATE_SUCCESS": function(state, action) {
        let {productId, desiredStock} = action.payload;
        let newState = Object.assign({}, state)
        let rowToChangeIndex = newState.visibleRows.findIndex(row => (row.id === productId));

        let visibleRowsCopy = newState.visibleRows.slice();
        visibleRowsCopy[rowToChangeIndex].stock.locations[0].onHand = desiredStock;

        return Object.assign({}, state, {
            visibleRows: visibleRowsCopy
        });
    },
    "GET_VARIATIONS_FROM_PRODUCTS": function(state, action) {
        let newState = Object.assign({}, state, {
            variationsByParent: fetchVariationsFromParents(state, action.payload.products)
        });
        return newState;
    }
});

export default ProductsReducer;

function applySingleProductLinkChangeToState(state, newLinks, sku) {
    const normalizedNewLinks = normalizeLinks(newLinks);
    const stateLinksCopy = Object.assign({}, state.allProductsLinks);

    const productIdFromSku = stateUtility.getProductIdFromSku(state.visibleRows, sku);

    if (!normalizedNewLinks[productIdFromSku]) {
        delete stateLinksCopy[productIdFromSku];
    } else {
        stateLinksCopy[productIdFromSku] = normalizedNewLinks[productIdFromSku];
    }

    let newState = Object.assign({}, state, {
        allProductsLinks: stateLinksCopy
    });

    return newState;
}

function applyNewProductLinksToState(state, newLinks) {
    const normalizedNewLinks = normalizeLinks(newLinks);
    const stateLinksCopy = Object.assign({}, state.allProductsLinks);

    let newProductLinks = Object.assign({}, stateLinksCopy, normalizedNewLinks);

    let newState = Object.assign({}, state, {
        allProductsLinks: newProductLinks
    });

    return newState;
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

function isSimpleProductLink(links, productId) {
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
    });
}

function changeExpandStatusForId(products, productId, desiredStatus) {
    let productRowIndex = products.findIndex((product) => {
        return product.id === productId;
    });
    products[productRowIndex].expandStatus = desiredStatus;

    return products;
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
    visibleRowsCopy.forEach((row, i) => {
        if (skusToFindLinkedProductsFor.indexOf(row.sku) < 0) {
            return;
        }
        visibleRowsCopy[i]["linkStatus"] = DESIRED_LINK_STATUS;
    });
    return visibleRowsCopy;
}

function addRowsForSingleProductExpansion(currentVisibleProducts, productRowIdToExpand, state) {
    let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowIdToExpand);
    let rowsToAdd = state.variationsByParent[productRowIdToExpand];

    currentVisibleProducts.splice(
        parentProductIndex + 1,
        0,
        ...rowsToAdd
    );
    currentVisibleProducts = changeExpandStatusForId(currentVisibleProducts, productRowIdToExpand, 'expanded');
    return currentVisibleProducts;
}

function collapseSingleProduct(currentVisibleProducts, productRowId, state) {
    let parentProductIndex = stateUtility.getProductIndex(currentVisibleProducts, productRowId);

    let numberOfRowsToRemove = state.variationsByParent[productRowId].length;

    currentVisibleProducts.splice(
        parentProductIndex + 1,
        numberOfRowsToRemove
    );

    currentVisibleProducts = changeExpandStatusForId(
        currentVisibleProducts,
        productRowId,
        'collapsed'
    );
    return currentVisibleProducts;
}

function fetchVariationsFromParents(state, products) {
    let variationsByParent = [];
    for (let product of products) {
        if (!stateUtility.isParentProduct(product)) {
            continue;
        }
        variationsByParent[product.id] = product.variations;
    }
    return variationsByParent;
}

function applyListingsToProducts(products, listings) {
    let productsWithListings = {};
    for (var index in products) {
        let product = products[index];
        let listingsData = getListingsData(product, listings);
        product.listings = listingsData;
        productsWithListings[index] = product;
    }
    return productsWithListings;
}

function getListingsData(product, listings) {
    let fullListingsData = {};
    for (var index in product.listings) {
        let listingId = listings[index].id;
        fullListingsData[listingId] = Object.assign({}, product.listings[index], listings[index]);
    }
    return fullListingsData;
}