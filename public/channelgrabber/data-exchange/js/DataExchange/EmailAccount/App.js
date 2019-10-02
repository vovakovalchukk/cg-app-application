import React from 'react';
import ReactDOM from 'react-dom';
import { applyMiddleware, createStore } from 'redux';
import { Provider } from 'react-redux';
import thunk from "redux-thunk";
import CombinedReducer from './Reducers/Combined';
import EmailAccountsComponent from "./Components/EmailAccounts";
import { EmailAccountTypeFrom, EmailAccountTypeTo } from "./Components/AccountsTable";

let enhancer = applyMiddleware(thunk);

if (typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) {
    enhancer = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({
        latency: 0,
        name: 'EmailAccounts',
        trace: true
    })(applyMiddleware(thunk));
}

let App = function (mountingNode, data) {
    let store = createStore(CombinedReducer, {
        emailAccounts: formatEmailAccounts(data.emailAccounts)
    }, enhancer);

    ReactDOM.render(React.createElement(
        Provider,
        { store: store },
        React.createElement(EmailAccountsComponent, null)
    ), mountingNode);
};

export default App;

const formatEmailAccounts = emailAccounts => {
    return {
        [EmailAccountTypeFrom]: formatAccountsForType(emailAccounts, EmailAccountTypeFrom),
        [EmailAccountTypeTo]: formatAccountsForType(emailAccounts, EmailAccountTypeTo)
    };
};

const formatAccountsForType = (accounts, type) => {
    let accountsForType = accounts.filter(account => {
        return account.type.toString().trim() === type;
    });

    return accountsForType.map(account => {
        return Object.assign(account, {
            newAddress: account.address
        });
    });
};
