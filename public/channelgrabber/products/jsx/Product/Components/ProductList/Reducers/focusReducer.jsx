import reducerCreator from 'Common/Reducers/creator';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';

"use strict";

let initialState = {
    focusedInputInfo: {}
};
let focusReducer = reducerCreator(initialState, {
    "INPUT_FOCUS": function(state, action) {
        let {focusedInputInfo} = action.payload;
        let stateCopy = Object.assign({}, state);
        stateCopy.focusedInputInfo = focusedInputInfo;
        return stateCopy;
    },
    "INPUT_BLUR": function(state, action) {
        let stateCopy = Object.assign({}, state);
        console.log('INPUT_BLUR');
        
        
        stateCopy.focusedInputInfo = {};
        return stateCopy;
    }
});

export default focusReducer;