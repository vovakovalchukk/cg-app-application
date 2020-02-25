import React from 'react';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from 'redux-thunk';
import combinedReducer from 'MessageCentre/Reducers/Combined';
import MessageCentreRoot from 'MessageCentre/Root';
import { BrowserRouter as Router, Route } from 'react-router-dom'

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
const store = createStore(
    combinedReducer,
    enhancer
);

const MessageCentreProvider = (props) => {
    return (
        <Provider
            store={store}
        >
            <Router>
                <Route path="/messages/" render={() => (
                    <MessageCentreRoot
                        {...props}
                    />
                )}/>
            </Router>
        </Provider>
    );
};

export default MessageCentreProvider;