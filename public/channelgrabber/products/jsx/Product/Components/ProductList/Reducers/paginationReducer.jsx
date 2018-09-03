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
            console.log('in PRODUCTS_GET_REQUEST_SUCCESS -r action.payload:',action.payload);
            let {pagination} = action.payload;
            let {limit, page, total} = pagination;
            let newState = Object.assign({}, state, {
                limit,
                page,
                total
            });
            console.log('newState being set: ', newState);
            return newState;
        },
        "LIMIT_CHANGE": function(state, action) {
            const {desiredLimit} = action.payload;
            let newState = Object.assign({}, state, {
                limit: desiredLimit
            });
            console.log('LIMIT_CHANGE -r newState',newState);
    
            return newState;
        }
    });
    
    return paginationReducer;
});