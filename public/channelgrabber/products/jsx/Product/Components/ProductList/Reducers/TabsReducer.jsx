define([
    'Common/Reducers/creator',
    'Product/Components/ProductList/stateFilters'
], function(
    reducerCreator,
    stateFilters
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
                key: 'listings',
                label: 'VAT'
            },
        ],
        currentTab: 'details'
    };
    
    var TabsReducer = reducerCreator(initialState, {
        "TAB_CHANGE": function(state, action) {
        
        }
    });
    
    return TabsReducer
});