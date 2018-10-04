import reducerCreator from 'Common/Reducers/creator';
"use strict";

var initialState = {
    productSearchActive: false,
    searchTerm: ''
};

var searchReducer = reducerCreator(initialState, {
    "PRODUCTS_SEARCH_TERM_SET": function(state, action) {
        let {searchTerm} = action.payload;
        let newState = Object.assign({}, state, {
            searchTerm
        });
        return newState;
    },
});

export default searchReducer