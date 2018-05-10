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
                if (!isFormValid(formValues)) {
                    return;
                }
                var formattedValues = formatFormValuesForPostRequest(formValues);
                console.log('formattedValues: ' , formattedValues);
                submitProductForCreation(formattedValues);
            }
        }
    };

    return actionCreators;

    function isFormValid(values) {
        console.log('in isFormValid with values: ', values);
        if (!values.variations || !values.title) {
            return false;
        }
        for (var key in values.variations) {
            if (key == 'c-table-with-inputs__headings') {
                continue;
            }
            if (!values.variations[key].sku) {
                return false;
            }
        }
        return true;
    }

    function formatFormValuesForPostRequest(values) {
        console.log('in formatFormValuesForPostRequest with values: ', values);
        var attributeNames = getAttributeNamesFromFormData(values);
        var formattedVariations = formatVariationFormValuesForPostRequest(values.variations, attributeNames);
        var formattedImages = formatImagesFormValuesForPostRequest(values);

        var formattedValues = {
            product: {
                name: values.title,
                imageIds: formattedImages,
                taxRateIds: {}, // TODO
                variations: formattedVariations
            }
        };
        return formattedValues;
    };

    function getAttributeNamesFromFormData(values) {
        var attributeNames = {};
        if (!values.variations['c-table-with-inputs__headings']) {
            return attributeNames;
        }
        for (var key in values.variations['c-table-with-inputs__headings']) {
            if (!key.match(/^custom-attribute/)) {
                continue;
            }
            attributeNames[key] = values.variations['c-table-with-inputs__headings'][key];
        }
        return attributeNames;
    };

    function formatVariationFormValuesForPostRequest(variations, attributeNames) {
        return Object.keys(variations).filter(function(key) {
            return (key != 'c-table-with-inputs__headings');
        }).map(function(key) {
            var formattedVariation = Object.assign({}, variations[key]);
            formattedVariation.stock = {
                stockMode: formattedVariation.stockModeType || null,
                stockLevel: formattedVariation.stockAmount || null
            };
            delete formattedVariation.stockModeType;
            delete formattedVariation.stockAmount;

            formattedVariation.attributeValues = {};
            for (var key in attributeNames) {
                if (formattedVariation[key]) {
                    formattedVariation.attributeValues[attributeNames[key]] = formattedVariation[key];
                    delete formattedVariation[key];
                }
            }
            return formattedVariation;
        });
    };

    function formatImagesFormValuesForPostRequest(values) {
        var imageIds = [];
        if (values.mainImage) {
            imageIds.push({
                imageId: values.mainImage.id,
                order: 1
            });
        }
        return imageIds;
    };

    function submitProductForCreation(values) {
        console.log('in submitProductForCreation with values: ', values);
        // TODO
    };
});
