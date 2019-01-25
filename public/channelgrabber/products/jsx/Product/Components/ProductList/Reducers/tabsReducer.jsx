import reducerCreator from 'Common/Reducers/creator';
"use strict";

let initialState = {
    tabs: [
        {
            key: 'listings',
            label: 'Listings'
        },
        {
            key: 'details',
            label: 'Details'
        },
        {
            key: 'vat',
            label: 'VAT'
        }
    ],
    currentTab: 'listings',
    currentColumnScrollIndex: null
};

let stockTab = {
    key: 'stock',
    label: 'Stock'
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
    },
    "SHOW_STOCK_TAB": function (state) {
        let newTabs = state.tabs;
        newTabs.push(stockTab);
        return Object.assign({}, state, {
            tabs: newTabs
        });
    }
});

export default TabsReducer