import reducerCreator from 'Common/Reducers/creator';

"use strict";

var initialState = {
    userScrolling : false,
    currentRowScrollIndex: null,
    currentColumnScrollIndex: null
};

var scrollReducer = reducerCreator(initialState, {
    "SET_USER_SCROLLING": function(state) {
        return Object.assign({}, state, {
            userScrolling: true
        });
    },
    "UNSET_USER_SCROLLING": function(state) {
        return Object.assign({}, state, {
            userScrolling: false
        });
    },
    "VERTICAL_SCROLLBAR_SET_TO_0": function(state) {
        return Object.assign({}, state, {
            currentRowScrollIndex: 0
        });
    },
    "HORIZONTAL_SCROLLBAR_INDEX_RESET": function(state) {
        return Object.assign({}, state, {
            currentColumnScrollIndex: null
        });
    },
    "HORIZONTAL_SCROLLBAR_INDEX_UPDATE": function(state, action) {
        let {index} = action.payload;
        return {
            ...state,
            currentColumnScrollIndex: index
        };
    }
});

export default scrollReducer