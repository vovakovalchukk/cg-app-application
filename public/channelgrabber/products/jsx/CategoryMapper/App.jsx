import React from 'react';
import ReactDOM from 'react-dom';
import {createStore} from 'redux';
import {Provider} from 'react-redux';
import RootContainer from 'CategoryMapper/Components/Root';
import CombinedReducer from 'CategoryMapper/Reducers/Combined';
    

    var extractAccountsFromRawData = function (data) {
        var accounts = {},
            newAccountData;
        for (var accountId in data.accounts) {
            newAccountData = Object.assign({}, data.accounts[accountId], {categories: null});
            delete(newAccountData['categories']);
            accounts[accountId] = newAccountData;
        }
        return accounts;
    }

    var extractCategoriesFromRawData = function (data) {
        var categories = {};
        data.categories.forEach(function (value, index) {
            categories[value.accountId] = Object.assign({}, value.categories);
        });
        return categories;
    }

    var App = function(mountingNode, data) {
        var store = createStore(
            CombinedReducer,
            {
                accounts: extractAccountsFromRawData(data),
                categories: extractCategoriesFromRawData(data)
            }
        );
        ReactDOM.render(
            <Provider store={store}>
                <RootContainer/>
            </Provider>,
            mountingNode
        );
    };

    export default App;

