define([
    'Common/Reducers/creator'
], function(
    reducerCreator
) {
    "use strict";
    
    let initialState = {
        createListingsAllowedChannels: [],
        createListingsAllowedVariationChannels: []
    };
    
    let CreateListingReducer = reducerCreator(initialState, {
        "PRODUCTS_GET_REQUEST_SUCCESS": function(state, action) {
            let {createListingsAllowedChannels, createListingsAllowedVariationChannels} = action.payload;
            
            let newState = Object.assign({}, state, {
                createListingsAllowedChannels,
                createListingsAllowedVariationChannels
            });
            return newState;
        }
    });
    
    return CreateListingReducer;
});