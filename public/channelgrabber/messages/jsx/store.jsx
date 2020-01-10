import {applyMiddleware, createStore} from 'redux';
import thunk from "redux-thunk";
import combinedReducer from "MessageCentre/Reducers/Combined";
import {useState} from "react";

import {initTemplates} from "MessageCentre/Reducers/templatesReducer";

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
    const initialState = {
        templates: initTemplates(props.messageTemplates)
    };

    return createStore(
        combinedReducer,
        initialState,
        enhancer
    );
};