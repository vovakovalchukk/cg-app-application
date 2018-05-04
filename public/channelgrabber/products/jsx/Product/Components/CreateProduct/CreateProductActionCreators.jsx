define([], function() {
    "use strict";
    var actionCreators = {
        initialAccountDataLoaded: function(taxRates, stockModeOptions) {
            return {
                type: 'INITIAL_ACCOUNT_DATA_LOADED',
                payload: {
                    taxRates: taxRates,
                    stockModeOptions: stockModeOptions
                }
            };
        },
        formSubmitRequest: function() {
            return function(dispatch, getState) {
                dispatch({
                    type: 'FORM_SUBMIT_REQUEST'
                });
                var formValues = getState().form.createProductForm.values;
                console.log('formValues: ', formValues);
                var formattedValues = formatFormValuesForPostRequest(formValues);
                console.log('formattedValues: ', formattedValues);

                if (!formattedValues) {
                    // values are not sufficient
                    dispatch({
                        type: 'FORM_SUBMIT_REJECTED'
                    });
                } else {
                    dispatch({
                        type: 'FORM_SUBMIT_DATA_POSTED'
                    });
                }
            }
        }
    };

    return actionCreators;

    function formatFormValuesForPostRequest(values) {
        console.log('in formatFormValuesForPostRequest with values: ', values);
        var formattedValues = {};

        if (!values.variations) {
            return null;
        }
        // if no product title return
        // if there are no variations with at least a product name
    }

});
