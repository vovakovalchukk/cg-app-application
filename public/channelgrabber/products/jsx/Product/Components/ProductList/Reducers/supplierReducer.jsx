import reducerCreator from 'Common/Reducers/creator';
import stateUtility from "../stateUtility";

"use strict";

const initialState = {
    options: [],
    byProductId: {}
};

const supplierReducer = reducerCreator(initialState, {
    "STORE_SUPPLIERS_OPTIONS": function (state, action) {
        const options = [];
        Object.keys(action.payload.options).forEach((supplierId) => {
            options.push({
                name: action.payload.options[supplierId],
                value: supplierId
            });
        });
        return Object.assign({}, state, {
            options
        });
    },
    "EXTRACT_SUPPLIERS": function (state, action) {
        let products = action.payload.products;
        const byProductId = Object.assign({}, state.byProductId);

        products.forEach((product) => {
            if (!product.details || !product.details.supplierId) {
                return;
            }
            byProductId[product.id] = product.details.supplierId;
        });

        return Object.assign({}, state, {
            byProductId
        });
    },
    "UPDATE_SUPPLIER_SUCCESS": function (state, action) {
        return Object.assign({}, state, {
            byProductId: updateSupplierIdForProduct(action.payload.product, state.byProductId, action.payload.supplierId)
        });
    },
    "SAVE_SUPPLIER_SUCCESS": function (state, action) {
        const byProductId = updateSupplierIdForProduct(action.payload.product, state.byProductId, action.payload.supplierId);

        const options = state.options.slice();
        options.unshift({
            name: action.payload.supplierName,
            value: action.payload.supplierId
        });

        return {
            options,
            byProductId
        }
    }
});

export default supplierReducer;

function updateSupplierIdForProduct(product, byProductId, supplierId) {
    const newByProductId = Object.assign({}, byProductId, {
        [product.id]: supplierId
    });

    if (stateUtility.isParentProduct(product)) {
        product.variationIds.forEach((variationId) => {
            newByProductId[variationId] = supplierId;
        });
    }

    return newByProductId;
}