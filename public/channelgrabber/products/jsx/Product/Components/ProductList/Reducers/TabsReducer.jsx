define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/stateUtility'
], function(
    reducerCreator,
    stateUtility
) {
    "use strict";
    
    var initialState = {
        tabs: [{
            key: 'listings',
            label: 'Listings'
        }, {
            key: 'details',
            label: 'Details'
        },
            {
                key: 'vat',
                label: 'VAT'
            },
        ],
        currentTab: 'listings'
    };
    
    var TabsReducer = reducerCreator(initialState, {
        "TAB_CHANGE": function(state, action) {
            let newState = Object.assign({}, state, {
                currentTab: action.payload.desiredTabKey
            });
            return newState;
        }
    });
    
    return TabsReducer
});