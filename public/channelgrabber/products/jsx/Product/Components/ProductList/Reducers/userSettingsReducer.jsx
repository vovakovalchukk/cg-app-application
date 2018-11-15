import reducerCreator from 'Common/Reducers/creator';

"use strict";

let initialState = {
    massUnit: '',
    lengthUnit: '',
    stockModeDefault: '',
    stockLevelDefault: ''
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
    }
});

export default userSettingsReducer