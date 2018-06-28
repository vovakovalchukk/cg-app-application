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
                    n.error("Please make sure you have given your product a name and have provided at least one variation with a SKU and quantity.");
                    return;
                }
                dispatch(submitForm);
            }
        },
        formSubmit: function(formValues, redirectToProducts) {
            return function(dispatch, getState) {
                let uploadedImages = getState().uploadedImages.images;
                let formattedImages = formatUploadedImagesForPostRequest(uploadedImages, formValues.mainImage);
                let formattedValues = formatFormValuesForPostRequest(formValues, formattedImages);
                dispatch({
                    type: 'FORM_SUBMIT_REQUEST'
                });
                submitFormViaAjax(dispatch, formattedValues, redirectToProducts);
            }
        },
        userLeavesCreateProduct: function() {
            return {
                type: 'USER_LEAVES_CREATE_PRODUCT'
            };
        }
    };

    return actionCreators;

    function isFormValid(values) {
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
            if (!values.variations[key].quantity) {
                return false;
            }
        }
        return true;
    }

    function formatUploadedImagesForPostRequest(uploadedImages, mainImage) {
        let sortedImages = uploadedImages;
        if (mainImage) {
            sortedImages = uploadedImages.sort((a, b) => {
                if (b.id == mainImage.id) {
                    return 1;
                }
            })
        }
        var formattedImages = sortedImages.map((image, index) => {
            return {
                imageId: image.id,
                order: index + 1
            }
        });
        return formattedImages
    }

    function formatFormValuesForPostRequest(values, formattedImages) {
        var formattedVariations = formatVariationFormValuesForPostRequest(
            values.variations,
            getAttributeNamesFromFormData(values),
            values.identifiers
        );
        var formattedValues = {
            product: {
                name: values.title,
                imageIds: formattedImages,
                taxRateIds: values.taxRates,
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

    function addProductIdentifiersToFormattedVariation(formattedVariation, productIdentifiers) {
        if (!productIdentifiers) {
            productIdentifiers = {};
        }
        let skuMatch = Object.keys(productIdentifiers).find((key) => {
            return key === formattedVariation.sku
        });

        if (!skuMatch) {
            return formattedVariation;
        }

        let variationIdentifiers = productIdentifiers[skuMatch];

        let mergedVariation = Object.assign(formattedVariation, variationIdentifiers);
        return mergedVariation;
    }

    function formatVariationFormValuesForPostRequest(variations, attributeNames, productIdentifiers) {
        return Object.keys(variations).filter(function(key) {
            return (key != 'c-table-with-inputs__headings');
        }).map(function(key) {
            var formattedVariation = Object.assign({}, variations[key]);

            delete formattedVariation.images;

            formattedVariation.stock = {
                stockMode: formattedVariation.stockModeType.value || null,
                stockLevel: formattedVariation.stockAmount || null
            };
            delete formattedVariation.stockModeType;
            delete formattedVariation.stockAmount;

            formattedVariation = addProductIdentifiersToFormattedVariation(formattedVariation, productIdentifiers);

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

    function submitFormViaAjax(dispatch, values, redirectToProducts) {
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
                n.success("Successfully saved new product.");
                redirectToProducts();
                dispatch(actionCreators.userLeavesCreateProduct())
            },
            error: function(xhr, status, errorThrown) {
                dispatch({
                    type: 'FORM_SUBMIT_ERROR',
                    payload: {
                        xhr: xhr,
                        status: status,
                        errorThrown: errorThrown
                    }
                });
                n.error("There was an issue with saving the new product. Please try again or contact support if the issue persists.")
            }
        })
    }
});
