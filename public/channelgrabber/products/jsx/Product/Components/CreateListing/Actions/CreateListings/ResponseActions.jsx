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
        listingFormSubmittedError: function (errors) {
            return {
                type: "LISTING_FORM_SUBMITTED_NOT_ALLOWED",
                payload: {
                    errors: errors
                }
            };
        }
    };
});
