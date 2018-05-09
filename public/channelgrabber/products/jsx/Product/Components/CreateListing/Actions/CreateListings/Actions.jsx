define([
    './ResponseActions'
], function(
    ResponseActions
) {
    "use strict";

    var formatFormValuesForSubmission = function (values) {
        return values;
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
        submitListingsForm: function (dispatch, formValues) {
            $.ajax({
                url: '/products/listing/submit',
                type: 'POST',
                data: formatFormValuesForSubmission(formValues),
                success: function(response) {
                    if (response.allowed) {
                        dispatch(ResponseActions.listingFormSubmittedSuccessfully(response.guid));
                    } else {
                        if (response.errors) {
                            dispatch(ResponseActions.listingFormSubmittedError(response.errors));
                        } else {
                            dispatch(ResponseActions.listingFormSubmittedNotAllowed());
                        }
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
