define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
) {
    "use strict";
    
    var initialState = {
        productSearchActive: false,
        searchTerm:''
    };
    
    var searchReducer = reducerCreator(initialState, {
    });
    
    return searchReducer
});