import React from 'react';
import ReactDOM from 'react-dom';
import {createStore} from 'redux';
import {applyMiddleware} from "redux";
import {Provider} from 'react-redux';
import thunk from "redux-thunk";
import CombinedReducer from './Reducers/Combined';
import EmailAccountsComponent from "./Components/EmailAccounts";

let enhancer = applyMiddleware(thunk);

if (typeof window === 'object' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'EmailAccounts',
        trace: true
    })(applyMiddleware(
        thunk
    ));
}

let App = function(mountingNode, data) {
    let store = createStore(
        CombinedReducer,
        {
            emailAccounts: formatEmailAccounts(data.emailAccounts)
        },
        enhancer
    );

    ReactDOM.render(
        <Provider store={store}>
            <EmailAccountsComponent/>
        </Provider>,
        mountingNode
    );
};

export default App;

const formatEmailAccounts = (emailAccounts) => {
    let emailAccountsObject = {};
    emailAccounts.forEach(emailAccount => {
        emailAccountsObject[emailAccount.id] = Object.assign(emailAccount, {
            newAddress: emailAccount.address
        });
    });
    return emailAccountsObject;
};
