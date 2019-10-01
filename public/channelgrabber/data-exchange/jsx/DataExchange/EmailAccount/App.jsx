import React from 'react';
import ReactDOM from 'react-dom';
import {createStore} from 'redux';
import {Provider} from 'react-redux';
import CombinedReducer from './Reducers/Combined';
import EmailAccountsComponent from "./Components/EmailAccounts";
    
let App = function(mountingNode, data) {
    let store = createStore(
        CombinedReducer,
        {
            emailAccounts: formatEmailAccounts(data.emailAccounts)
        }
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
