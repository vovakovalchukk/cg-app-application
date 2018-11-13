import reducerCreator from 'Common/Reducers/creator';
"use strict";

let initialState = {
    massUnit: '',
    lengthUnit: ''
};

var userSettingsReducer = reducerCreator(initialState, {
    "METRICS_STORE": function(state, action) {
        let {massUnit, lengthUnit} = action.payload;
        let newState = Object.assign({}, state, {
            massUnit,
            lengthUnit
        });
        return newState;
    }
});

export default userSettingsReducer