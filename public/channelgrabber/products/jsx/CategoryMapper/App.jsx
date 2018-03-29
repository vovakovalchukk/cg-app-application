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

    var mergeDataIntoAccounts = function (data) {
        var categories = [];
        for (var accountId in data.accounts) {
            categories = {};
            for (var category of data.categories) {
                if (category.accountId == accountId) {
                    categories = category.categories;
                    break;
                }
            }
            data.accounts[accountId].categories = [categories];
        }
        return data.accounts;
    };

    var App = function(mountingNode, data) {
        var Provider = ReactRedux.Provider;
        var store = Redux.createStore(
            CombinedReducer,
            {
                categoryMap: [
                    {categoryMap: mergeDataIntoAccounts(data)},
                    {categoryMap: mergeDataIntoAccounts(data)}
                ]
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
