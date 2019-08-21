import {submit as reduxFormSubmit} from 'redux-form';
import fieldService from 'Product/Components/CreateListing/Service/field';

var submitForm = reduxFormSubmit('createProductForm');

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

    export default actionCreators;

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
        return {
            product: {
                name: values.title,
                imageIds: formattedImages,
                taxRateIds: values.taxRates,
                variations: formattedVariations
            }
        };
    };

    function getAttributeNamesFromFormData(values) {
        var attributeNames = {};
        if (!values.attributes) {
            return attributeNames;
        }
        for (var key in values.attributes) {
            attributeNames[key] = values.attributes[key];
        }
        return attributeNames;
    };

    function addProductIdentifiersToFormattedVariation(formattedVariation, productIdentifiers) {
        if (!productIdentifiers) {
            productIdentifiers = {};
        }
        let skuMatch = Object.keys(productIdentifiers).find((key) => {
            let prefixedId = fieldService.getVariationIdWithPrefix(formattedVariation.id);
            return key === prefixedId;
        });

        if (!skuMatch) {
            return formattedVariation;
        }

        let variationIdentifiers = productIdentifiers[skuMatch];
        delete variationIdentifiers.id;

        let mergedVariation = Object.assign(formattedVariation, variationIdentifiers);
        delete mergedVariation.id;
        return mergedVariation;
    }

    function addAttributeValuesToFormattedVariation(formattedVariation, attributeNames) {
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
    }

    function addStockToFormattedVariation(formattedVariation) {
        formattedVariation.stock = {
            stockMode: formattedVariation.stockModeType.value || null,
            stockLevel: formattedVariation.stockAmount || null
        };
        delete formattedVariation.stockModeType;
        delete formattedVariation.stockAmount;
        return formattedVariation;
    }

    function formatVariationFormValuesForPostRequest(variations, attributeNames, productIdentifiers) {
        return Object.keys(variations).map(function(key) {
            var formattedVariation = Object.assign({}, variations[key]);

            delete formattedVariation.images;

            formattedVariation = addStockToFormattedVariation(formattedVariation)
            formattedVariation = addProductIdentifiersToFormattedVariation(formattedVariation, productIdentifiers);
            formattedVariation = addAttributeValuesToFormattedVariation(formattedVariation, attributeNames);
            delete formattedVariation.id;
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

