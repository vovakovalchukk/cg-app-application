import React from 'react';
import {createStore} from 'redux';
import {Provider} from 'react-redux';
import CombinedReducer from 'Product/Components/CreateListing/Reducers/Combined';
import AccountSelectionPopup from 'Product/Components/CreateListing/AccountSelectionPopup';
import CreateListingUtils from 'Product/Utils/CreateListingUtils';
    

    var CreateListingRoot = function(
        accounts,
        allowedChannels,
        allowedVariationChannels,
        productSearchActive,
        productSearchActiveForVariations,
        onCreateListingClose,
        ebaySiteOptions,
        categoryTemplateOptions,
        renderCreateListingPopup,
        renderSearchPopup,
        product,
        listingCreationAllowed,
        managePackageUrl,
        salesPhoneNumber,
        demoLink
    ) {
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

        var store = createStore(
            CombinedReducer,
            buildInitialStateFromData()
        );

        class CreateListingRootComponent extends React.Component {
            render() {
                return (
                    <Provider store={store}>
                        <AccountSelectionPopup
                            onCreateListingClose={onCreateListingClose}
                            onSubmit={this.onSubmit}
                            ebaySiteOptions={ebaySiteOptions}
                            product={product}
                            renderCreateListingPopup={renderCreateListingPopup}
                            renderSearchPopup={renderSearchPopup}
                            listingCreationAllowed={listingCreationAllowed}
                            managePackageUrl={managePackageUrl}
                            salesPhoneNumber={salesPhoneNumber}
                            demoLink={demoLink}
                            productSearchActive={productSearchActive}
                            productSearchActiveForVariations={productSearchActiveForVariations}
                        />
                    </Provider>
                );
            }
        }

        return CreateListingRootComponent;
    };

    export default CreateListingRoot;

