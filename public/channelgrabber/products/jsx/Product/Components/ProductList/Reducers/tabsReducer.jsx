define([
    'Common/Reducers/creator'
], function(
    reducerCreator,
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
        currentTab: 'listings',
        currentColumnScrollIndex: null
    };
    
    var TabsReducer = reducerCreator(initialState, {
        "TAB_CHANGE": function(state, action) {
            let newState = Object.assign({}, state, {
                currentTab: action.payload.desiredTabKey,
                currentColumnScrollIndex: action.payload.numberOfVisibleFixedColumns
            });
            return newState;
        },
        "HORIZONTAL_SCROLLBAR_INDEX_RESET": function(state) {
            let newState = Object.assign({}, state, {
                currentColumnScrollIndex: null
            });
            return newState;
        }
    });
    
    return TabsReducer
});