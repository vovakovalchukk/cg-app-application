define([
    'redux-form'
], function(
    ReduxForm
) {
    "use strict";

    var submitForm = ReduxForm.submit('createProductForm');


    console.log('submitForm:  ' , submitForm);

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
                dispatch(submitForm);

                var formValues = getState().form.createProductForm.values;
                var formattedValues = formatFormValuesForPostRequest(formValues);
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
