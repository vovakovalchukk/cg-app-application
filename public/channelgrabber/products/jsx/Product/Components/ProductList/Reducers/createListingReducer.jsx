import reducerCreator from 'Common/Reducers/creator';

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

export default CreateListingReducer;