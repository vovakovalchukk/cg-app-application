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
        currentColumnScrollIndex: undefined
    };
    
    var TabsReducer = reducerCreator(initialState, {
        "TAB_CHANGE": function(state, action) {
            let newState = Object.assign({}, state, {
                currentTab: action.payload.desiredTabKey,
                // todo needs to be calculated by the length of the visible fixed columns
                // could just be state.columns.length.
                currentColumnScrollIndex:action.payload.numberOfVisibleFixedColumns
            });
            return newState;
        },
        "SCROLLBAR_INDEX_RESET": function(state,action){
            let newState = Object.assign({}, state, {
                currentColumnScrollIndex: null
            });
            return newState;
        }
    });
    
    return TabsReducer
});