import {applyMiddleware, createStore} from 'redux';
import thunk from "redux-thunk";
import combinedReducer from "MessageCentre/Reducers/Combined";
import {useState} from "react";

let enhancer = applyMiddleware(thunk);

if (typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'MessageCentre',
        trace: true
    })(applyMiddleware(
        thunk
    ));
}

export default function initializeStore(props) {
    console.log('props in initializeStore: ', props);

    let initialState = {
        templates: {test: ' something'}
    };

    return createStore(
        combinedReducer,
        initialState,
        enhancer
    );
};