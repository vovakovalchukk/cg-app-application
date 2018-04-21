define([
    'react',
    'redux',
    'react-redux',
    'Product/Components/CreateListing/Reducers/Combined',
    'Product/Components/CreateListing/AccountSelectionPopup',
    'Product/Utils/CreateListingUtils'
], function (
    React,
    Redux,
    ReactRedux,
    CombinedReducer,
    AccountSelectionPopup,
    CreateListingUtils
) {
    "use strict";

    var CreateListingRoot = function(
        accounts,
        allowedChannels,
        onCreateListingClose
    ) {
        var Provider = ReactRedux.Provider;

        var getAccountOptions = function(accounts, allowedChannels) {
            var data = {};
            for (var accountId in accounts) {
                var account = accounts[accountId];
                if (CreateListingUtils.productCanListToAccount(account, allowedChannels)) {
                    data[accountId] = {name: account.displayName, id: account.id, channel: account.channel};
                }
            }

            return data;
        };

        var buildInitialStateFromData = function() {
            return {
                accounts: getAccountOptions(accounts, allowedChannels)
            };
        };

        var store = Redux.createStore(
            CombinedReducer,
            buildInitialStateFromData()
        );

        var CreateListingRootComponent = React.createClass({
            onSubmit: function() {
                console.log(arguments);
            },
            render: function () {
                return (
                    <Provider store={store}>
                        <AccountSelectionPopup
                            onCreateListingClose={onCreateListingClose}
                            onSubmit={this.onSubmit}
                        />
                    </Provider>
                );
            }
        });

        return CreateListingRootComponent;
    };

    return CreateListingRoot;
});
