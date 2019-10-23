import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    userScrolling : false,
    currentRowScrollIndex: null,
    currentColumnScrollIndex: null
};

var scrollReducer = reducerCreator(initialState, {
    "SET_USER_SCROLLING": function(state) {
        let newState = Object.assign({}, state, {
            userScrolling: true
        });
        return newState;
    },
    "UNSET_USER_SCROLLING": function(state) {
        let newState = Object.assign({}, state, {
            userScrolling: false
        });
        return newState;
    },
    "VERTICAL_SCROLLBAR_SET_TO_0": function(state) {
        let newState = Object.assign({}, state, {
            currentRowScrollIndex: 0
        });
        return newState;
    },
    "HORIZONTAL_SCROLLBAR_INDEX_RESET": function(state) {
        let newState = Object.assign({}, state, {
            currentColumnScrollIndex: null
        });
        return newState;
    },
    "HORIZONTAL_SCROLLBAR_INDEX_UPDATE": function(state, action) {
        let {index} = action.payload;
        let newState = {
            ...state,
            currentColumnScrollIndex: index
        };
        return newState;
    }
});

export default scrollReducer