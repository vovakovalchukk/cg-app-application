define([
    'react',
    'react-dom',
    'redux',
    'react-redux',
    'CategoryMapper/Components/Root',
    'CategoryMapper/Reducers/CategoryMap'
], function(
    React,
    ReactDOM,
    Redux,
    ReactRedux,
    RootContainer,
    CategoryMapReducer
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

    var CreateNewApp = function(mountingNode, data) {
        var Provider = ReactRedux.Provider;
        var store = Redux.createStore(CategoryMapReducer);
        ReactDOM.render(
            <Provider store={store}>
                <RootContainer
                    accounts={mergeDataIntoAccounts(data)}
                />
            </Provider>,
            mountingNode
        );
    };

    return CreateNewApp;
});
