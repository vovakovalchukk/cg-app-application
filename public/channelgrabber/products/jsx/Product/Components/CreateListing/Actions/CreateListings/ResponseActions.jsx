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
        }
    };
});
