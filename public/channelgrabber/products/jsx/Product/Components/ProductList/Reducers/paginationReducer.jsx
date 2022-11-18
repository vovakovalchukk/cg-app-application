import reducerCreator from 'Common/Reducers/creator';
"use strict";

let getters = (function() {
    return {
        getPage: (state) => state.page,
    }
}());

var initialState = {
    limit: 50,
    page: 1,
    total: null
};

initialState = Object.assign(initialState, getters);

var paginationReducer = reducerCreator(initialState, {
    "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
        let {pagination} = action.payload;
        let {limit, page, total} = pagination;
        let newState = Object.assign({}, state, {
            limit,
            page,
            total
        });
        return newState;
    },
    "LIMIT_CHANGE": function(state, action) {
        const {desiredLimit} = action.payload;
        let newState = Object.assign({}, state, {
            limit: desiredLimit
        });
        return newState;
    },
    "INITIAL_PAGINATION": (state, action) => {
        if (action.payload == null) {
            return state;
        }
        return {
            ...state,
            ...action.payload
        };
    }
});

export default paginationReducer;