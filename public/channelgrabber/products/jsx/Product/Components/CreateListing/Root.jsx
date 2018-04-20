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
        channelBadges,
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

        var getFilteredChannelBadges = function (channelBadges, allowedChannels) {
            var badges = {};
            for (var channel in allowedChannels) {
                badges[channel] = channelBadges[channel];
            }
            return badges;
        };

        var buildInitialStateFromData = function() {
            return {
                accounts: getAccountOptions(accounts, allowedChannels),
                channelBadges: getFilteredChannelBadges(channelBadges, allowedChannels)
            };
        };

        var store = Redux.createStore(
            CombinedReducer,
            buildInitialStateFromData()
        );

        var CreateListingRootComponent = React.createClass({
            render: function () {
                return (
                    <Provider store={store}>
                        <AccountSelectionPopup
                            onCreateListingClose={onCreateListingClose}
                        />
                    </Provider>
                );
            }
        });

        return CreateListingRootComponent;
    };

    return CreateListingRoot;
});
