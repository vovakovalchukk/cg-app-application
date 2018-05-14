define([
    './ResponseActions'
], function(
    ResponseActions
) {
    "use strict";

    var formatFormValuesForSubmission = function(values, props) {
        return {
            accountIds: props.accounts,
            categoryTemplateIds: props.categories,
            product: {
                id: props.product.id,
                etag: props.product.eTag,
                imageId: values.imageId ? values.imageId[0] : null,
                variations: formatVariationData(values, props),
                title: values.title,
                description: values.description,
                brand: values.brand,
                condition: values.condition,
                productChannelDetail: formatProductChannelDetail(values, props),
                productCategoryDetail: formatProductCategoryDetail(values, props)
            },
            accountCategories: formatAccountCategoryMap(props)
        };
    };

    var formatVariationData = function(values, props) {
        var variations = [];
        for (var sku in values.identifiers) {
            variations.push(
                Object.assign(
                    values.identifiers[sku],
                    values.dimensions[sku],
                    {
                        sku: sku,
                        productAccountDetail: formatProductAccountDetailsPrices(values.prices[sku])
                    }
                )
            );
        }
        return variations;
    };

    var formatProductAccountDetailsPrices = function(prices) {
        var result = [];
        for (var accountId in prices) {
            result.push({
                accountId: accountId,
                price: prices[accountId]
            });
        }
        return result;
    };

    var formatProductChannelDetail = function(values, props) {
        if (!values.channel) {
            return [];
        }
        var details = [];
        for (var channelName in values.channel) {
            details.push(Object.assign({}, formatProductChannelDataForChannel(values.channel[channelName]), {
                channel: channelName
            }));
        }
        return details;
    };

    var formatProductChannelDataForChannel = function (values) {
        values = Object.assign({}, values);
        if (values.attributeImageMap && Object.keys(values.attributeImageMap).length > 0) {
            var attributeImageMap = {};
            Object.keys(values.attributeImageMap).forEach(attributeValue => {
                attributeImageMap[attributeValue] = values.attributeImageMap[attributeValue].slice().pop();
            });
            values.attributeImageMap = attributeImageMap;
        }
        return values;
    };

    var formatProductCategoryDetail = function(values, props) {
        if (!values.category|| Object.keys(values.category).length === 0) {
            return [];
        }
        var details = [];
        for (var categoryId in values.category) {
            var category = values.category[categoryId];
            var categoryDetail = Object.assign({}, category, {
                categoryId: categoryId
            });

            categoryDetail.itemSpecifics = formatItemSpecificsForCategory(categoryDetail.itemSpecifics);

            details.push(categoryDetail);
        }
        return details;
    };

    var formatItemSpecificsForCategory = function(itemSpecifics) {
        if (!itemSpecifics) {
            return {};
        }

        itemSpecifics = Object.assign({}, itemSpecifics);
        if (itemSpecifics.customItemSpecifics && itemSpecifics.customItemSpecifics instanceof Array) {
            itemSpecifics.customItemSpecifics.forEach(function(itemSpecific) {
                if (itemSpecific.name && itemSpecific.value) {
                    itemSpecifics[itemSpecific.name] = itemSpecific.value;
                }
            });
        }

        delete itemSpecifics.customItemSpecifics;
        delete itemSpecifics.optionalItemSpecifics;
        return itemSpecifics;
    };

    var formatAccountCategoryMap = function (props) {
        var accounts = props.submissionStatuses.accounts;
        if (Object.keys(accounts).length === 0) {
            return [];
        }

        var accountCategories = [];
        for (var accountId in accounts) {
            var account = accounts[accountId];
            for (var categoryId in account) {
                var category = account[categoryId];
                if (category.status !== "error") {
                    continue;
                }
                accountCategories.push({
                    accountId: accountId,
                    categoryId: categoryId
                });
            }
        }
        return accountCategories;
    };

    var progressPolling = {
        polling: null,
        fetchListingProgress: function(dispatch, guid) {
            $.ajax({
                context: this,
                url: '/products/listing/submitMultiple/progress/' + guid,
                type: 'GET',
                success: function(response) {
                    dispatch(ResponseActions.listingProgressFetched(response.accounts));
                    if (progressPolling.shouldStopPolling(response.accounts)) {
                        progressPolling.stopPolling();
                        dispatch(ResponseActions.listingSubmissionFinished());
                    }
                }
            });
        },
        startListingProgressPolling: function(dispatch, guid) {
            this.polling = setInterval(progressPolling.fetchListingProgress, 5000, dispatch, guid);
        },
        stopPolling: function () {
            clearInterval(progressPolling.polling);
        },
        shouldStopPolling: function(accounts) {
            if (Object.keys(accounts).length === 0) {
                return true;
            }

            for (var accountId in accounts) {
                var account = accounts[accountId];
                for (var categoryId in account.categories) {
                    var category = account.categories[categoryId];
                    if (category.status === "started") {
                        return false;
                    }
                }
            }

            return true;
        }
    };

    return {
        loadInitialValues: function(product, variationData, accounts, accountDefaultSettings, accountsData, categoryTemplates) {
            return {
                type: "LOAD_INITIAL_VALUES",
                payload: {
                    product: product,
                    variationData: variationData,
                    selectedAccounts: accounts,
                    accountDefaultSettings: accountDefaultSettings,
                    accountsData: accountsData,
                    categoryTemplates: categoryTemplates
                }
            };
        },
        submitListingsForm: function (dispatch, formValues, props) {
            $.ajax({
                url: '/products/listing/submitMultiple',
                type: 'POST',
                data: formatFormValuesForSubmission(formValues, props),
                success: function(response) {
                    if (response.allowed) {
                        dispatch(ResponseActions.listingFormSubmittedSuccessfully(response.guid));
                        progressPolling.startListingProgressPolling(dispatch, response.guid);
                    } else {
                        dispatch(ResponseActions.listingFormSubmittedNotAllowed());
                    }
                },
                error: function() {
                    dispatch(ResponseActions.listingFormSubmittedError(
                        "An unknown error has occurred. Please try again or contact support if the problem persists"
                    ));
                }
            });

            n.notice("Please wait while we are creating the listings on the selected channels, it might take a while...", true);
            return {
                type: "SUBMIT_LISTING_FORM",
                payload: {
                    formValues: formValues
                }
            };
        }
    };
});
