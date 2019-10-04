import React from 'react';
import ReactDOM from 'react-dom';
import {applyMiddleware, createStore} from 'redux';
import {Provider} from 'react-redux';
import thunk from "redux-thunk";
import CombinedReducer from './Reducers/Combined';
import FtpAccountsComponent from "./Components/FtpAccounts";

let enhancer = applyMiddleware(thunk);

if (typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'FtpAccounts',
        trace: true
    })(applyMiddleware(
        thunk
    ));
}

let App = function(mountingNode, data) {
    let store = createStore(
        CombinedReducer,
        {
            ftpAccounts: data.ftpAccounts
        },
        enhancer
    );

    ReactDOM.render(
        <Provider store={store}>
            <FtpAccountsComponent
                accountTypeOptions={data.accountTypeOptions}
                defaultPorts={data.defaultPorts}
            />
        </Provider>,
        mountingNode
    );
};

export default App;