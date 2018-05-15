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
        allowedVariationChannels,
        onCreateListingClose,
        ebaySiteOptions,
        categoryTemplateOptions,
        renderCreateListingPopup,
        product
    ) {
        var Provider = ReactRedux.Provider;

        var getAccountOptions = function(accounts, allowedChannels, allowedVariationChannels) {
            var channels = allowedChannels;
            if (product.variationCount > 0) {
                channels = allowedVariationChannels;
            }
            var data = {};
            for (var accountId in accounts) {
                var account = accounts[accountId];
                if (CreateListingUtils.productCanListToAccount(account, channels)) {
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
                accounts: getAccountOptions(accounts, allowedChannels, allowedVariationChannels),
                categoryTemplateOptions: buildCategoryTemplateOptions(categoryTemplateOptions)
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
                            onSubmit={this.onSubmit}
                            ebaySiteOptions={ebaySiteOptions}
                            product={product}
                            renderCreateListingPopup={renderCreateListingPopup}
                        />
                    </Provider>
                );
            }
        });

        return CreateListingRootComponent;
    };

    return CreateListingRoot;
});
