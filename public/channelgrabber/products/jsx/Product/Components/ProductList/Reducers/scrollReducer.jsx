import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    userScrolling : false
};

var scrollReducer = reducerCreator(initialState, {
    "SET_USER_SCROLLING": function(state) {
        console.log('setting user scrolling to true');
        let newState = Object.assign({}, state, {
            userScrolling: true
        });
        return newState;
    },
    "UNSET_USER_SCROLLING": function(state) {
        console.log('setting user scrolling to true');
        let newState = Object.assign({}, state, {
            userScrolling: false
        });
        return newState;
    }
});

export default scrollReducer