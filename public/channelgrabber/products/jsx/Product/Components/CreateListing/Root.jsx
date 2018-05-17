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
        onCreateListingClose,
        ebaySiteOptions,
        categoryTemplateOptions,
        renderCreateListingPopup,
        listingCreationAllowed
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

        var buildCategoryTemplateOptions = function(categoryTemplateOptions) {
            var categories = {};
            for (var categoryId in categoryTemplateOptions) {
                categories[categoryId] = Object.assign(categoryTemplateOptions[categoryId], {
                    selected: false
                });
            }
            return categories;
        }

        var buildInitialStateFromData = function() {
            return {
                accounts: getAccountOptions(accounts, allowedChannels),
                categoryTemplateOptions: buildCategoryTemplateOptions(categoryTemplateOptions)
            };
        };

        var store = Redux.createStore(
            CombinedReducer,
            buildInitialStateFromData()
        );

        var CreateListingRootComponent = React.createClass({
            getDefaultProps: function() {
                return {
                    product: {}
                }
            },
            render: function () {
                return (
                    <Provider store={store}>
                        <AccountSelectionPopup
                            onCreateListingClose={onCreateListingClose}
                            onSubmit={this.onSubmit}
                            ebaySiteOptions={ebaySiteOptions}
                            product={this.props.product}
                            renderCreateListingPopup={renderCreateListingPopup}
                            listingCreationAllowed={listingCreationAllowed}
                        />
                    </Provider>
                );
            }
        });

        return CreateListingRootComponent;
    };

    return CreateListingRoot;
});
