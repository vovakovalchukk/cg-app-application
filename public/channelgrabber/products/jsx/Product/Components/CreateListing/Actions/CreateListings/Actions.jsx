define([
    './ResponseActions'
], function(
    ResponseActions
) {
    "use strict";

    var formatFormValuesForSubmission = function(values, props) {
        let valuesForSubmission = {
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

        console.log('valuesForSubmision: ', valuesForSubmission);
        return valuesForSubmission
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

    let formatProductChannelDataForChannel = function(values) {
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

    let formatThemeDetails = function(details) {
        let formattedDetails = [];
        details.forEach((detailsObject) => {
            let detailsWithValidValues = detailsObject;
            detailsWithValidValues.validValues = formatValidValues(detailsObject.theme);
            delete detailsWithValidValues.theme;
            formattedDetails.push(detailsWithValidValues);
        });
        return formattedDetails;
    };

    let formatValidValues = function(themeData) {
        let formattedValidValues = {};

        for (let sku in themeData) {
            let currentSku = themeData[sku];
            formattedValidValues[sku] = [];

            for (let attributeData in currentSku) {
                let currentAttributeData = currentSku[attributeData];
                let formattedAttribute = {
                    displayName: currentAttributeData.displayName
                };
                delete currentAttributeData.displayName;

                for (var attribute in currentAttributeData) {
                    formattedAttribute["name"] = attribute;
                    formattedAttribute["option"] = currentAttributeData[attribute];
                    break;
                }
                formattedValidValues[sku].push(formattedAttribute);
            }
        }

        return formattedValidValues;
    };

    var formatProductCategoryDetail = function(values, props) {
        if (!values.category || Object.keys(values.category).length === 0) {
            return [];
        }
        var details = [];
        for (var categoryId in values.category) {
            var category = values.category[categoryId];
            var categoryDetail = Object.assign({}, category, {
                categoryId: categoryId
            });

            categoryDetail.itemSpecifics = formatItemSpecificsForCategory(categoryDetail.itemSpecifics);
            if (categoryDetail.subcategory) {
                categoryDetail.subCategoryId = formatSubCategoryId(categoryDetail.subcategory);
                delete categoryDetail.subcategory;
            }

            details.push(categoryDetail);
        }

        details = formatThemeDetails(details);

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

        if (itemSpecifics.selectedChoice) {
            itemSpecifics = {
                [itemSpecifics.selectedChoice]: itemSpecifics[itemSpecifics.selectedChoice]
            };
        }

        delete itemSpecifics.customItemSpecifics;
        delete itemSpecifics.optionalItemSpecifics;
        var itemSpecific;
        Object.keys(itemSpecifics).forEach(key => {
            itemSpecific = itemSpecifics[key];
            if (typeof itemSpecific !== 'object' || itemSpecific === null) {
                return;
            }
            itemSpecifics[key] = formatItemSpecificsForCategory(itemSpecific);
        });
        return itemSpecifics;
    };

    var formatSubCategoryId = function(subcategory) {
        var subCategoryId = 0;
        subcategory.forEach(category => {
            if (!category || !(category.selected) || !(category.selected.value)) {
                return;
            }
            subCategoryId = category.selected.value;
        });
        return subCategoryId;
    };

    var formatAccountCategoryMap = function(props) {
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
            progressPolling.stopPolling();
            this.polling = setInterval(progressPolling.fetchListingProgress, 5000, dispatch, guid);
        },
        stopPolling: function() {
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
        submitListingsForm: function(dispatch, formValues, props) {
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
