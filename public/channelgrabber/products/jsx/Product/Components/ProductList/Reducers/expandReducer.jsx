import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    expandAllStatus: '',
};

var expandReducer = reducerCreator(initialState, {
    "EXPAND_ALL_TOGGLE": function(state){
        console.log('in EXPAND_ALL_TOGGLE');
        //
        
        return Object.assign({}, state, {

        });
    }
});

export default expandReducer;