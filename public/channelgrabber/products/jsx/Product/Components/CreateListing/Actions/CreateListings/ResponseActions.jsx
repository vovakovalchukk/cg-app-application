define([], function() {
    "use strict";

    return {
        listingFormSubmittedSuccessfully: function(guid) {
            return {
                type: "LISTING_FORM_SUBMITTED_SUCCESSFUL",
                payload: {
                    guid: guid
                }
            };
        },
        listingFormSubmittedNotAllowed: function() {
            return {
                type: "LISTING_FORM_SUBMITTED_NOT_ALLOWED",
                payload: {}
            };
        },
        listingFormSubmittedError: function(error) {
            return {
                type: "LISTING_FORM_SUBMITTED_ERROR",
                payload: {
                    error: error
                }
            };
        },
        listingProgressFetched: function(accounts) {
            return {
                type: "LISTING_PROGRESS_FETCHED",
                payload: {
                    accounts: accounts
                }
            };
        },
        listingSubmissionFinished: function() {
            return {
                type: "LISTING_SUBMISSION_FINISHED",
                payload: {}
            };
        },
        accountPoliciesFetched: function (accountId, response) {
            return {
                type: "ACCOUNT_POLICIES_FETCHED",
                payload: {
                    accountId: accountId,
                    policies: {
                        returnPolicies: response.returnPolicies,
                        paymentPolicies: response.paymentPolicies,
                        shippingPolicies: response.shippingPolicies
                    }
                }
            }
        },
        accountPoliciesFetchError: function () {
            return {
                type: "ACCOUNT_POLICIES_FETCH_ERROR",
                payload: {}
            }
        }
    };
});
