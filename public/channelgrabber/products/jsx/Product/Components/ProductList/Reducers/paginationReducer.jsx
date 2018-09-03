define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        limit: 50,
        page: 1,
        total: null
    };
    
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
            console.log('LIMIT_CHANGE action.payload: ', action.payload);
            const {desiredLimit} = action.payload;
            let newState = Object.assign({}, state, {
                limit: desiredLimit
            });
            console.log('newState: ', newState);
            
            return newState;
        }
    });
    
    return paginationReducer;
});