define([
    './ResponseActions'
], function(
    ResponseActions
) {
    "use strict";

    var formatFormValuesForSubmission = function(values, props) {
        console.log(values, props);
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
            }
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
            details.push(Object.assign({}, values.channel[channelName], {
                channel: channelName
            }));
        }
        return details;
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
                    } else {
                        dispatch(ResponseActions.listingFormSubmittedNotAllowed());
                    }
                },
                error: function() {
                    dispatch(ResponseActions.listingFormSubmittedError([
                        "An unknown error has occurred. Please try again or contact support if the problem persists"
                    ]));
                }
            });

            return {
                type: "SUBMIT_LISTING_FORM",
                payload: {
                    formValues: formValues
                }
            };
        }
    };
});
