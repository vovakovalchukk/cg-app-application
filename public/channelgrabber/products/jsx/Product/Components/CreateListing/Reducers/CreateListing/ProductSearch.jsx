import reducerCreator from 'Common/Reducers/creator';
    let initialState = {
        isFetching: false,
        products: {},
        selectedProducts: {},
        error: false
    };

    export default reducerCreator(initialState, {
        "FETCH_SEARCH_RESULTS": function(state) {
            return Object.assign({}, state, {
                isFetching: true,
                products: {}
            });
        },
        "SEARCH_RESULTS_FETCHED": function(state, action) {
            if (action.payload.products.length === 0) {
                n.notice('No products found.');
            }
            return Object.assign({}, state, {
                isFetching: false,
                products: action.payload.products
            });
        },
        "SEARCH_RESULTS_ERROR": function(state) {
            return Object.assign({}, state, {
                isFetching: false,
                products: {}
            });
        },
        "ASSIGN_SEARCH_PRODUCT_TO_CG_PRODUCT": function(state, action) {
            let selectedProducts = state.selectedProducts;
            let existingId;

            Object.keys(selectedProducts).forEach(function(id) {
                let selectedProduct = selectedProducts[id];
                if (selectedProduct.epid == action.payload.searchProduct.epid) {
                    existingId = id;
                }
            });

            if (existingId) {
                delete selectedProducts[existingId];
            }

            selectedProducts = Object.assign({}, state.selectedProducts, {
                [action.payload.cgProduct]: action.payload.searchProduct
            });

            return Object.assign({}, state, {
                selectedProducts: selectedProducts
            });
        },
        "REVERT_TO_INITIAL_VALUES": function() {
            return Object.assign({}, initialState);
        },
        "CLEAR_SELECTED_PRODUCT": function(state, action) {
            let selectedProducts = JSON.parse(JSON.stringify(state.selectedProducts));
            delete selectedProducts[action.payload.productId];

            return Object.assign({}, state, {
                selectedProducts: selectedProducts
            });
        },
        "ADD_ERROR_PRODUCT_SEARCH": function(state, action) {
            return Object.assign({}, state, {
                error: action.payload.error
            });
        },
        "CLEAR_ERROR_PRODUCT_SEARCH": function(state) {
            if (state.error === false) {
                return state;
            }

            return Object.assign({}, state, {
                error: false
            });
        }
    });

