import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    massUnit: '',
    lengthUnit: '',
    stockModeDefault: '',
    stockLevelDefault: '',
    lowStockThresholdToggle: false,
    lowStockThresholdValue: null
};

var userSettingsReducer = reducerCreator(initialState, {
    "METRICS_STORE": function(state, action) {
        let {massUnit, lengthUnit} = action.payload;
        let newState = Object.assign({}, state, {
            massUnit,
            lengthUnit
        });
        return newState;
    },
    "STOCK_DEFAULTS_STORE": function(state, action) {
        let {stockModeDefault, stockLevelDefault} = action.payload;
        let newState = Object.assign({}, state, {
            stockModeDefault,
            stockLevelDefault
        });
        return newState;
    },
    "LOW_STOCK_DEFAULT_THRESHOLD": function(state, action) {
        return Object.assign({}, state, action.payload);
    }
});

export default userSettingsReducer