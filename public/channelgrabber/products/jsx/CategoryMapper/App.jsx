define([
    'react',
    'react-dom',
    'redux',
    'react-redux',
    'CategoryMapper/Components/Root',
    'CategoryMapper/Reducers/Combined'
], function(
    React,
    ReactDOM,
    Redux,
    ReactRedux,
    RootContainer,
    CombinedReducer
) {
    "use strict"

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
        var Provider = ReactRedux.Provider;
        var store = Redux.createStore(
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

    return App;
});
