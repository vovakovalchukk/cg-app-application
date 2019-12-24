import reducerCreator from 'Common/Reducers/creator';

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
        const byProductId = {};

        products.forEach((product) => {
            if (!product.details || !product.details.supplierId) {
                return;
            }
            byProductId[product.id] = {
                supplierId: product.details.supplierId
            };
        });

        return Object.assign({}, state, {
            byProductId
        });
    },
    "UPDATE_SUPPLIER_SUCCESS": function (state, action) {
        const byProductId = Object.assign({}, state.byProductId, {
            [action.payload.productId]: action.payload.supplierId
        });

        return Object.assign({}, state, {
            byProductId
        });
    },
    "UPDATE_SUPPLIER_FAILED": function (state, action) {
        n.error(action.payload.error);
        return state;
    }
});

export default supplierReducer;