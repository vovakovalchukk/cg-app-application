define([
    'redux-form'
], function(
    ReduxForm
) {
    "use strict";
    var submitForm = ReduxForm.submit('createProductForm');

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
        formContainerSubmitClick: function() {
            return function(dispatch, getState) {
                dispatch({
                    type: 'FORM_CONTAINER_SUBMIT_CLICK'
                });
                var formValues = getState().form.createProductForm.values;
                if (!isFormValid(formValues)) {
                    dispatch({
                        type: 'FORM_SUBMIT_INVALID_FORM'
                    });
                    return;
                }
                dispatch(submitForm);
            }
        },
        formSubmit: function(formValues,redirectToProducts) {
            return function(dispatch) {
                var formattedValues = formatFormValuesForPostRequest(formValues);
                dispatch({
                    type: 'FORM_SUBMIT_REQUEST'
                });
                submitFormViaAjax(dispatch,formattedValues,redirectToProducts);
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
                if (!formattedVariation.hasOwnProperty(key)) {
                    continue;
                }
                if (formattedVariation[key] !== null) {
                    formattedVariation.attributeValues[attributeNames[key]] = formattedVariation[key];
                }
                delete formattedVariation[key];
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

    function submitFormViaAjax(dispatch,values,redirectToProducts){
        $.ajax({
            url: '/products/create/save',
            data: values,
            type: 'POST',
            context: this,
            dataType: "json",
            success: function() {
                dispatch({
                    type: 'FORM_SUBMIT_SUCCESS'
                });
                n.success("successfully saved new product");
                redirectToProducts();
                dispatch({
                    type: 'USER_LEAVES_CREATE_PRODUCT'
                })

            },
            error: function(xhr, status, errorThrown) {
                dispatch({
                    type: 'FORM_SUBMIT_ERROR',
                    payload: {
                        xhr:xhr,
                        status:status,
                        errorThrown:errorThrown
                    }
                });
                n.error("error saving new product")
            }
        })
    }
});
