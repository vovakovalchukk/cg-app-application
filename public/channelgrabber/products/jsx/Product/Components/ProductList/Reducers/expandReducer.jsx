import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    expandAllStatus: 'collapsed'
};

var expandReducer = reducerCreator(initialState, {
    "EXPAND_ALL_STATUS_CHANGE": function(state, action){
        let {desiredStatus} = action.payload;
        console.log('in EXPAND_ALL_TOGGLE');

        return Object.assign({}, state, {
            expandAllStatus: desiredStatus
        });
    }
});

export default expandReducer;